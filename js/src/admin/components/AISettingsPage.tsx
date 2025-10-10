import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Select from 'flarum/common/components/Select';
import Stream from 'flarum/common/utils/Stream';

interface ProviderCatalog {
  text: ModelOption[];
  embeddings: ModelOption[];
  moderation: ModelOption[];
}

interface ModelOption {
  value: string;
  label: string;
  capability: string[];
  is_default?: boolean;
}

interface ApiResponse {
  success: boolean;
  data: any;
  error?: string;
}

export default class AISettingsPage extends ExtensionPage {
  catalog: Record<string, ProviderCatalog> = {};
  currentProvider = Stream('openai');

  textModel = Stream('gpt-4o-mini');
  embeddingsModel = Stream('text-embedding-3-small');
  moderationModel = Stream('omni-moderation-latest');

  customTextModel = Stream('');
  customEmbeddingsModel = Stream('');
  customModerationModel = Stream('');

  isTestingText = Stream(false);
  isTestingEmbeddings = Stream(false);
  isTestingModeration = Stream(false);

  oninit(vnode: any) {
    super.oninit(vnode);

    this.currentProvider(this.setting('datlechin-ai.provider', 'openai')());

    const textSelected = this.setting('datlechin-ai.models.selected.text', 'gpt-4o-mini')();
    const textCustom = this.setting('datlechin-ai.models.custom.text', '')();
    this.textModel(textSelected);
    this.customTextModel(textCustom);

    const embeddingsSelected = this.setting('datlechin-ai.models.selected.embeddings', 'text-embedding-3-small')();
    const embeddingsCustom = this.setting('datlechin-ai.models.custom.embeddings', '')();
    this.embeddingsModel(embeddingsSelected);
    this.customEmbeddingsModel(embeddingsCustom);

    const moderationSelected = this.setting('datlechin-ai.models.selected.moderation', 'omni-moderation-latest')();
    const moderationCustom = this.setting('datlechin-ai.models.custom.moderation', '')();
    this.moderationModel(moderationSelected);
    this.customModerationModel(moderationCustom);

    this.loadCatalog();
  }

  async loadCatalog() {
    try {
      const response = await app.request<ApiResponse>({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + '/ai/provider-catalog',
      });

      if (response && response.success) {
        this.catalog = response.data;

        if (this.customTextModel()) {
          this.textModel('__custom__');
        }
        if (this.customEmbeddingsModel()) {
          this.embeddingsModel('__custom__');
        }
        if (this.customModerationModel()) {
          this.moderationModel('__custom__');
        }

        m.redraw();
      }
    } catch (error) {
      console.error('Failed to load provider catalog:', error);
    }
  }

  content() {
    return (
      <div className="AISettingsPage">
        <div className="container">
          <div className="Form">
            <div className="Form-group">
              <label>{app.translator.trans('datlechin-ai.admin.settings.provider_label')}</label>
              <Select
                value={this.currentProvider()}
                options={{
                  openai: app.translator.trans('datlechin-ai.admin.settings.provider_openai'),
                  gemini: app.translator.trans('datlechin-ai.admin.settings.provider_gemini'),
                  anthropic: app.translator.trans('datlechin-ai.admin.settings.provider_anthropic'),
                }}
                onchange={(value: string) => {
                  this.currentProvider(value);
                  this.setting('datlechin-ai.provider')(value);
                  this.updateModelsForProvider(value);
                }}
              />
            </div>

            <div className="Form-group">
              <label>
                {app.translator.trans('datlechin-ai.admin.settings.api_key_label', {
                  provider: this.currentProvider(),
                })}
              </label>
              {this.buildSettingComponent({
                type: 'text',
                setting: `datlechin-ai.${this.currentProvider()}.api_key`,
                placeholder: app.translator.trans('datlechin-ai.admin.settings.api_key_placeholder', {
                  provider: this.currentProvider(),
                }),
              })}
            </div>

            <div className="Form-group">
              <label>{app.translator.trans('datlechin-ai.admin.settings.base_url_label')}</label>
              {this.buildSettingComponent({
                type: 'text',
                setting: `datlechin-ai.${this.currentProvider()}.base_url`,
                placeholder: app.translator.trans('datlechin-ai.admin.settings.base_url_placeholder'),
              })}
            </div>

            {this.renderModelSelect('text', this.textModel, this.customTextModel)}
            {this.currentProvider() !== 'anthropic' && this.renderModelSelect('embeddings', this.embeddingsModel, this.customEmbeddingsModel)}
            {this.currentProvider() === 'openai' && this.renderModelSelect('moderation', this.moderationModel, this.customModerationModel)}

            <div className="Form-group">
              <Button className="Button" onclick={() => this.testTextModel()} loading={this.isTestingText()} disabled={this.isTestingText()}>
                {app.translator.trans('datlechin-ai.admin.settings.test_text_button')}
              </Button>{' '}
              {this.currentProvider() !== 'anthropic' && (
                <Button
                  className="Button"
                  onclick={() => this.testEmbeddings()}
                  loading={this.isTestingEmbeddings()}
                  disabled={this.isTestingEmbeddings()}
                >
                  {app.translator.trans('datlechin-ai.admin.settings.test_embeddings_button')}
                </Button>
              )}{' '}
              {this.currentProvider() === 'openai' && (
                <>
                  <Button
                    className="Button"
                    onclick={() => this.testModeration()}
                    loading={this.isTestingModeration()}
                    disabled={this.isTestingModeration()}
                  >
                    {app.translator.trans('datlechin-ai.admin.settings.test_moderation_button')}
                  </Button>{' '}
                </>
              )}
            </div>

            {this.submitButton()}
          </div>
        </div>
      </div>
    );
  }

  renderModelSelect(capability: string, stream: Stream<string>, customStream: Stream<string>) {
    const provider = this.currentProvider();
    const providerCatalog = this.catalog[provider];

    if (!providerCatalog?.[capability as keyof ProviderCatalog]) {
      return null;
    }

    const models = providerCatalog[capability as keyof ProviderCatalog] || [];
    const options: Record<string, string> = {};

    models.forEach((model: ModelOption) => {
      options[model.value] = model.label;
    });

    options['__custom__'] = 'Custom Model';

    const isCustom = stream() === '__custom__';

    return (
      <div className="Form-group">
        <label>{app.translator.trans(`datlechin-ai.admin.settings.${capability}_model_label`)}</label>
        <span class="Select">
          <select
            className="Select-input FormControl"
            value={stream()}
            onchange={(e: any) => {
              const value = e.target.value;
              stream(value);
              this.setting(`datlechin-ai.models.selected.${capability}`)(value);

              if (value !== '__custom__') {
                customStream('');
                this.setting(`datlechin-ai.models.custom.${capability}`)('');
              }
              m.redraw();
            }}
          >
            {Object.keys(options).map((key) => (
              <option value={key} selected={key === stream()}>
                {options[key]}
              </option>
            ))}
          </select>
          <i aria-hidden="true" class="icon fas fa-sort Select-caret"></i>
        </span>

        {isCustom && (
          <div className="Form-group" style={{ marginTop: '10px' }}>
            <input
              type="text"
              className="FormControl"
              placeholder={app.translator.trans('datlechin-ai.admin.settings.custom_model_placeholder')}
              value={customStream()}
              oninput={(e: any) => {
                customStream(e.target.value);
                this.setting(`datlechin-ai.models.custom.${capability}`)(e.target.value);
              }}
            />
          </div>
        )}
      </div>
    );
  }

  updateModelsForProvider(provider: string) {
    const catalog = this.catalog[provider];
    if (!catalog) return;

    const textDefault = catalog.text?.find((m: ModelOption) => m.is_default)?.value || catalog.text?.[0]?.value;
    const embeddingsDefault = catalog.embeddings?.find((m: ModelOption) => m.is_default)?.value || catalog.embeddings?.[0]?.value;
    const moderationDefault = catalog.moderation?.find((m: ModelOption) => m.is_default)?.value || catalog.moderation?.[0]?.value;

    if (textDefault && !this.customTextModel()) {
      this.textModel(textDefault);
      this.setting('datlechin-ai.models.selected.text')(textDefault);
    }
    if (embeddingsDefault && !this.customEmbeddingsModel()) {
      this.embeddingsModel(embeddingsDefault);
      this.setting('datlechin-ai.models.selected.embeddings')(embeddingsDefault);
    }
    if (moderationDefault && !this.customModerationModel()) {
      this.moderationModel(moderationDefault);
      this.setting('datlechin-ai.models.selected.moderation')(moderationDefault);
    }

    m.redraw();
  }

  async testTextModel() {
    if (this.isTestingText()) return;

    this.isTestingText(true);
    m.redraw();

    try {
      const response = await app.request<ApiResponse>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/ai/generate',
        body: {
          messages: [{ role: 'user', content: 'Hello! Please respond with a brief greeting.' }],
        },
      });

      if (response && response.success) {
        app.alerts.show({ type: 'success' }, app.translator.trans('datlechin-ai.admin.settings.test_success'));
      }
    } catch (error: any) {
      app.alerts.show({ type: 'error' }, app.translator.trans('datlechin-ai.admin.settings.test_failed', { error: error.message }));
    } finally {
      this.isTestingText(false);
      m.redraw();
    }
  }

  async testEmbeddings() {
    if (this.isTestingEmbeddings()) return;

    this.isTestingEmbeddings(true);
    m.redraw();

    try {
      const response = await app.request<ApiResponse>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/ai/embeddings',
        body: {
          texts: ['This is a test embedding.'],
        },
      });

      if (response && response.success) {
        app.alerts.show({ type: 'success' }, app.translator.trans('datlechin-ai.admin.settings.test_success'));
      }
    } catch (error: any) {
      app.alerts.show({ type: 'error' }, app.translator.trans('datlechin-ai.admin.settings.test_failed', { error: error.message }));
    } finally {
      this.isTestingEmbeddings(false);
      m.redraw();
    }
  }

  async testModeration() {
    if (this.isTestingModeration()) return;

    this.isTestingModeration(true);
    m.redraw();

    try {
      const response = await app.request<ApiResponse>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/ai/moderate',
        body: {
          text: 'This is a test message for moderation.',
        },
      });

      if (response && response.success) {
        app.alerts.show({ type: 'success' }, app.translator.trans('datlechin-ai.admin.settings.test_success'));
      }
    } catch (error: any) {
      app.alerts.show({ type: 'error' }, app.translator.trans('datlechin-ai.admin.settings.test_failed', { error: error.message }));
    } finally {
      this.isTestingModeration(false);
      m.redraw();
    }
  }
}
