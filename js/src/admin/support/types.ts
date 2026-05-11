export type Capability = 'text' | 'embeddings' | 'moderation';

export interface ModelOption {
  value: string;
  label: string;
  dimension?: number | null;
}

export interface ProviderManifest {
  name: string;
  label: string;
  capabilities: Capability[];
  models: Record<Capability, ModelOption[]>;
  defaults: Partial<Record<Capability, string>>;
}

export const CUSTOM_MODEL = '__custom__';
