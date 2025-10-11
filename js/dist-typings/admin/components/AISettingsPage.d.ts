/// <reference types="mithril" />
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
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
export default class AISettingsPage extends ExtensionPage {
    catalog: Record<string, ProviderCatalog>;
    currentProvider: any;
    textModel: any;
    embeddingsModel: any;
    moderationModel: any;
    customTextModel: any;
    customEmbeddingsModel: any;
    customModerationModel: any;
    isTestingText: any;
    isTestingEmbeddings: any;
    isTestingModeration: any;
    oninit(vnode: any): void;
    loadCatalog(): Promise<void>;
    content(): JSX.Element;
    renderModelSelect(capability: string, stream: Stream<string>, customStream: Stream<string>): JSX.Element | null;
    updateModelsForProvider(provider: string): void;
    hasCapability(capability: keyof ProviderCatalog): boolean;
    testTextModel(): Promise<void>;
    testEmbeddings(): Promise<void>;
    testModeration(): Promise<void>;
}
export {};
