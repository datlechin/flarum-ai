import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import FieldSet from 'flarum/common/components/FieldSet';
import Form from 'flarum/common/components/Form';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import ItemList from 'flarum/common/utils/ItemList';
import type { SaveSubmitEvent } from 'flarum/admin/components/AdminPage';
import type { AlertIdentifier } from 'flarum/common/states/AlertManagerState';
import type Mithril from 'mithril';

export interface DriverField {
  setting: string;
  type: string;
  label: string;
  help: string | null;
  placeholder: string | null;
  required: boolean;
}

export interface AiSettings {
  drivers: Record<string, { label: string; fields: DriverField[] }>;
  errors: Record<string, string[]>;
  models: { ids: string[]; error: string | null };
}

interface TestReply {
  text: string;
  model: string;
  seconds: number;
}

export default class AiPage extends ExtensionPage {
  status?: AiSettings;
  testing = false;
  testAlert?: AlertIdentifier;

  oninit(vnode: Mithril.Vnode<any, this>) {
    super.oninit(vnode);

    this.refresh();
  }

  refresh() {
    this.loading = true;

    app
      .request<{ data: { attributes: AiSettings } }>({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + '/datlechin-ai-settings',
      })
      .then((response) => {
        this.status = response.data.attributes;
        this.loading = false;
        m.redraw();
      });
  }

  content() {
    return (
      <div className="ExtensionPage-settings AiPage">
        <div className="container">{this.loading || !this.status ? <LoadingIndicator /> : this.forms()}</div>
      </div>
    );
  }

  forms(): Mithril.Children {
    return (
      <>
        <Form>
          {this.settingItems().toArray()}
          <div className="Form-group Form-controls">{this.submitButton()}</div>
        </Form>
        <Form>
          <FieldSet
            className="FieldSet--col"
            label={app.translator.trans('datlechin-ai.admin.page.test_heading')}
            description={app.translator.trans('datlechin-ai.admin.page.test_text')}
          >
            <div>
              <Button className="Button Button--primary" loading={this.testing} disabled={this.isChanged()} onclick={this.sendTestPrompt.bind(this)}>
                {app.translator.trans('datlechin-ai.admin.page.test_button')}
              </Button>
            </div>
          </FieldSet>
        </Form>
      </>
    );
  }

  settingItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();
    const { drivers, errors } = this.status!;
    const name = this.setting('datlechin-ai.driver')();
    const driver = drivers[name];
    // The list was fetched with the saved provider's key, so it doesn't apply until a new choice is saved.
    const models = app.data.settings['datlechin-ai.driver'] === name ? this.status!.models : { ids: [], error: null };

    if (Object.keys(errors).length) {
      items.add('status', <Alert dismissible={false}>{app.translator.trans('datlechin-ai.admin.page.not_configured_message')}</Alert>, 100);
    }

    items.add(
      'driver',
      this.buildSettingComponent({
        type: 'select',
        setting: 'datlechin-ai.driver',
        label: app.translator.trans('datlechin-ai.admin.page.driver_label'),
        options: Object.fromEntries(Object.entries(drivers).map(([key, { label }]) => [key, app.translator.trans(label, {}, true)])),
      }),
      90
    );

    if (driver) {
      const modelSetting = `datlechin-ai.${name}.model`;
      const modelErrors = models.error
        ? [app.translator.trans('datlechin-ai.admin.page.models_error', { message: models.error }, true), ...(errors[modelSetting] || [])]
        : errors[modelSetting];

      items.add(
        'driverSettings',
        <FieldSet className="FieldSet--form">
          {driver.fields.map((field) =>
            this.field(
              {
                type: field.type,
                setting: field.setting,
                label: app.translator.trans(field.label),
                help: field.help ? app.translator.trans(field.help) : undefined,
                placeholder: field.placeholder ?? undefined,
                required: field.required,
                autocomplete: 'off',
              },
              errors[field.setting]
            )
          )}
          {this.field(
            {
              type: 'text',
              setting: modelSetting,
              label: app.translator.trans('datlechin-ai.admin.fields.model_label'),
              help: app.translator.trans('datlechin-ai.admin.fields.model_help'),
              list: 'datlechin-ai-models',
              required: true,
              autocomplete: 'off',
            },
            modelErrors
          )}
          <datalist id="datlechin-ai-models">
            {models.ids.map((id) => (
              <option value={id} />
            ))}
          </datalist>
        </FieldSet>,
        80
      );
    }

    return items;
  }

  field(attrs: Record<string, unknown>, errors?: string[]): Mithril.Children {
    return (
      <>
        {this.buildSettingComponent(attrs as any)}
        {errors && errors.length > 0 && (
          <div className="ValidationError">
            {errors.map((error) => (
              <p>{error}</p>
            ))}
          </div>
        )}
      </>
    );
  }

  sendTestPrompt() {
    this.testing = true;

    if (this.testAlert) app.alerts.dismiss(this.testAlert);

    app
      .request<TestReply>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/datlechin-ai/test',
      })
      .then((reply) => {
        this.testAlert = app.alerts.show(
          { type: 'success' },
          app.translator.trans('datlechin-ai.admin.page.test_success_message', { model: reply.model, seconds: reply.seconds, text: reply.text })
        );
      })
      .finally(() => {
        this.testing = false;
        m.redraw();
      });
  }

  saveSettings(e: SaveSubmitEvent) {
    return super.saveSettings(e).then(() => this.refresh());
  }
}
