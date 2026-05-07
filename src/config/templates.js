/**
 * Shared template configuration.
 * Used by both Templates page and BumpModal.
 */

import {
  StandardPreview,
  CompactPreview,
  HeroPreview,
  RibbonPreview,
  SplitPreview,
  FloatingPreview,
  TimelinePreview,
  SocialPreview
} from '../components/templates';

/**
 * Common style fields — shown for every template.
 */
export const commonStyleFields = [
  { key: 'backgroundColor', label: 'Background', type: 'color' },
  { key: 'textColor', label: 'Text Color', type: 'color' },
  { key: 'descriptionColor', label: 'Description', type: 'color' },
  { key: 'priceColor', label: 'Price Color', type: 'color' },
  { key: 'buttonColor', label: 'Button', type: 'color' },
  { key: 'buttonTextColor', label: 'Button Text', type: 'color' },
  { key: 'borderColor', label: 'Border Color', type: 'color' },
  { key: 'borderStyle', label: 'Border Style', type: 'select', options: [
    { value: 'solid', label: 'Solid' },
    { value: 'dashed', label: 'Dashed' },
    { value: 'none', label: 'None' },
  ]},
  { key: 'borderWidth', label: 'Border Width', type: 'number', unit: 'px', min: 0, max: 5 },
  { key: 'borderRadius', label: 'Border Radius', type: 'number', unit: 'px', min: 0, max: 24 },
  { key: 'padding', label: 'Padding', type: 'number', unit: 'px', min: 4, max: 32 },
];

/**
 * Template-specific style fields — only shown for the matching template.
 */
export const templateStyleFields = {
  standard: [
    { key: 'headerBgColor', label: 'Header Background', type: 'color' },
    { key: 'headerTextColor', label: 'Header Text', type: 'color' },
    { key: 'ctaBgColor', label: 'CTA Background', type: 'color' },
    { key: 'ctaBorderColor', label: 'CTA Border', type: 'color' },
  ],
  compact: [
    { key: 'saveBadgeColor', label: 'Save Badge', type: 'color' },
  ],
  hero: [],
  ribbon: [
    { key: 'ribbonColor', label: 'Ribbon Color', type: 'color' },
    { key: 'accentColor', label: 'Accent Color', type: 'color' },
  ],
  split: [
    { key: 'toggleColor', label: 'Toggle Color', type: 'color' },
    { key: 'starColor', label: 'Star Color', type: 'color' },
  ],
  floating: [
    { key: 'badgeColor', label: 'Badge Color', type: 'color' },
    { key: 'badgeTextColor', label: 'Badge Text', type: 'color' },
  ],
  timeline: [
    { key: 'accentColor', label: 'Accent Color', type: 'color' },
  ],
  social: [
    { key: 'ctaBgColor', label: 'CTA Background', type: 'color' },
    { key: 'ctaBorderColor', label: 'CTA Border', type: 'color' },
  ],
};

// Which page context each template is suited for:
// 'cart'     → only shown when a cart position is selected
// 'checkout' → only shown when a checkout position is selected
// 'both'     → always available
export const CART_POSITIONS = ['cart_before_totals', 'cart_after_totals'];

export const templateList = [
  {
    id: 'standard', name: 'Standard', isPro: false, context: 'both',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#16a34a', buttonColor: '#3b82f6', buttonTextColor: '#374151',
      borderColor: '#93c5fd', borderStyle: 'dashed', borderWidth: 1, borderRadius: 8, padding: 12,
      headerBgColor: '#3b82f6', headerTextColor: '#ffffff',
      ctaBgColor: '#eff6ff', ctaBorderColor: '#bfdbfe',
    },
  },
  {
    id: 'compact', name: 'Compact', isPro: false, context: 'both',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#9ca3af',
      priceColor: '#111827', buttonColor: '#3b82f6', buttonTextColor: '#ffffff',
      borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 12,
      saveBadgeColor: '#059669',
    },
  },
  {
    id: 'social', name: 'Social Proof', isPro: false, context: 'both',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#059669', buttonColor: '#10b981', buttonTextColor: '#1f2937',
      borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 10,
      ctaBgColor: '#ecfdf5', ctaBorderColor: '#a7f3d0',
    },
  },
  {
    id: 'split', name: 'Split View', isPro: false, context: 'both',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#0d9488', buttonColor: '#14b8a6', buttonTextColor: '#ffffff',
      borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 0,
      toggleColor: '#14b8a6', starColor: '#facc15',
    },
  },
  {
    id: 'hero', name: 'Hero Card', isPro: true, context: 'checkout',
    defaultStyles: {
      backgroundColor: '#4f46e5', textColor: '#ffffff', descriptionColor: '#c7d2fe',
      priceColor: '#ffffff', buttonColor: '#ffffff', buttonTextColor: '#4f46e5',
      borderColor: '#4f46e5', borderStyle: 'none', borderWidth: 1, borderRadius: 8, padding: 16,
    },
  },
  {
    id: 'ribbon', name: 'Ribbon Banner', isPro: true, context: 'checkout',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#111827', buttonColor: '#f59e0b', buttonTextColor: '#ffffff',
      borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 12,
      ribbonColor: '#ef4444', accentColor: '#d97706',
    },
  },
  {
    id: 'floating', name: 'Floating Card', isPro: true, context: 'checkout',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#f43f5e', buttonColor: '#f43f5e', buttonTextColor: '#ffffff',
      borderColor: '#f3f4f6', borderStyle: 'solid', borderWidth: 1, borderRadius: 12, padding: 12,
      badgeColor: '#f43f5e', badgeTextColor: '#ffffff',
    },
  },
  {
    id: 'timeline', name: 'Timeline Offer', isPro: true, context: 'checkout',
    defaultStyles: {
      backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
      priceColor: '#7c3aed', buttonColor: '#8b5cf6', buttonTextColor: '#ffffff',
      borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 12,
      accentColor: '#8b5cf6',
    },
  },
];

export const getDefaultStyles = (templateId) => {
  const tpl = templateList.find(t => t.id === templateId);
  return tpl ? { ...tpl.defaultStyles } : {
    backgroundColor: '#ffffff', textColor: '#1f2937', descriptionColor: '#6b7280',
    priceColor: '#16a34a', buttonColor: '#3b82f6', buttonTextColor: '#ffffff',
    borderColor: '#e5e7eb', borderStyle: 'solid', borderWidth: 1, borderRadius: 8, padding: 12,
  };
};

export const previewComponents = {
  standard: StandardPreview,
  compact: CompactPreview,
  hero: HeroPreview,
  ribbon: RibbonPreview,
  split: SplitPreview,
  floating: FloatingPreview,
  timeline: TimelinePreview,
  social: SocialPreview
};
