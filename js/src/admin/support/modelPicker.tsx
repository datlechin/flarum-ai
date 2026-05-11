import app from 'flarum/admin/app';
import type Mithril from 'mithril';
import { CUSTOM_MODEL, type Capability, type ProviderManifest } from './types';

export default function modelPicker(capability: Capability) {
  return function (this: any): Mithril.Children {
    const provider = String(this.setting('datlechin-ai.provider', 'openai')());
    const manifests = (app.data['datlechin-ai.providers'] || {}) as Record<string, ProviderManifest>;
    const manifest = manifests[provider];
    const models = manifest?.models?.[capability] ?? [];

    if (models.length === 0) return null;

    const selectedKey = `datlechin-ai.models.${capability}`;
    const customKey = `datlechin-ai.models.custom.${capability}`;
    const defaultModel = manifest.defaults[capability] ?? models[0]!.value;

    const options: Record<string, string> = {};
    models.forEach((m) => {
      options[m.value] = m.label;
    });
    options[CUSTOM_MODEL] = app.translator.trans('datlechin-ai.admin.settings.custom_model_option') as string;

    const selected = String(this.setting(selectedKey, defaultModel)());
    const isCustom = selected === CUSTOM_MODEL;

    return [
      this.buildSettingComponent({
        setting: selectedKey,
        type: 'select',
        label: app.translator.trans(`datlechin-ai.admin.settings.${capability}_model_label`),
        help: app.translator.trans(`datlechin-ai.admin.settings.${capability}_model_help`),
        options,
        default: defaultModel,
      }),
      isCustom
        ? this.buildSettingComponent({
            setting: customKey,
            type: 'text',
            placeholder: app.translator.trans('datlechin-ai.admin.settings.custom_model_placeholder'),
          })
        : null,
    ];
  };
}
