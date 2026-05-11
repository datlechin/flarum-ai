import app from 'flarum/admin/app';
import type { ProviderManifest } from './types';

export default function providerOptions(): Record<string, string> {
  const manifests = (app.data['datlechin-ai.providers'] || {}) as Record<string, ProviderManifest>;
  const options: Record<string, string> = {};

  Object.values(manifests).forEach((manifest) => {
    options[manifest.name] = manifest.label;
  });

  return options;
}
