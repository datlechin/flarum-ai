import ExtensionPage from 'flarum/admin/components/ExtensionPage';
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
    drivers: Record<string, {
        label: string;
        fields: DriverField[];
    }>;
    errors: Record<string, string[]>;
    models: {
        ids: string[];
        error: string | null;
    };
}
export default class AiPage extends ExtensionPage {
    status?: AiSettings;
    testing: boolean;
    testAlert?: AlertIdentifier;
    oninit(vnode: Mithril.Vnode<any, this>): void;
    refresh(): void;
    content(): JSX.Element;
    forms(): Mithril.Children;
    settingItems(): ItemList<Mithril.Children>;
    field(attrs: Record<string, unknown>, errors?: string[]): Mithril.Children;
    sendTestPrompt(): void;
    saveSettings(e: SaveSubmitEvent): Promise<void>;
}
