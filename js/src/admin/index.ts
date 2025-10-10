import app from 'flarum/admin/app';
import AISettingsPage from './components/AISettingsPage';

app.initializers.add('nqd/ai-core', () => {
  app.extensionData
    .for('datlechin-ai')
    .registerPage(AISettingsPage);
});
