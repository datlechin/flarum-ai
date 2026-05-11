import app from 'flarum/admin/app';
import type Mithril from 'mithril';
import type { ProviderManifest } from './types';

export default function apiKeyFields(this: any): Mithril.Children {
  const provider = String(this.setting('datlechin-ai.provider', 'openai')());
  const manifests = (app.data['datlechin-ai.providers'] || {}) as Record<string, ProviderManifest>;
  const manifest = manifests[provider];

  if (!manifest) return null;

  return [
    this.buildSettingComponent({
      setting: `datlechin-ai.${provider}.api_key`,
      type: 'password',
      label: app.translator.trans('datlechin-ai.admin.settings.api_key_label'),
      help: app.translator.trans('datlechin-ai.admin.settings.api_key_help', { provider: manifest.label }),
    }),
    this.buildSettingComponent({
      setting: `datlechin-ai.${provider}.base_url`,
      type: 'text',
      label: app.translator.trans('datlechin-ai.admin.settings.base_url_label'),
      help: app.translator.trans('datlechin-ai.admin.settings.base_url_help'),
      placeholder: app.translator.trans('datlechin-ai.admin.settings.base_url_placeholder'),
    }),
  ];
}
