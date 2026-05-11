import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';
import providerOptions from './support/providerOptions';
import modelPicker from './support/modelPicker';
import apiKeyFields from './support/apiKeyFields';

const t = (key: string) => app.translator.trans(`datlechin-ai.admin.settings.${key}`);

export default [
  new Extend.Admin()
    .setting(() => ({
      setting: 'datlechin-ai.provider',
      type: 'select',
      label: t('provider_label'),
      help: t('provider_help'),
      options: providerOptions(),
      default: 'openai',
    }))

    .customSetting(apiKeyFields)
    .customSetting(modelPicker('text'))
    .customSetting(modelPicker('embeddings'))
    .customSetting(modelPicker('moderation')),
];
