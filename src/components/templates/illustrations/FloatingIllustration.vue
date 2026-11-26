<template>
  <svg viewBox="0 0 260 170" width="260" height="170" xmlns="http://www.w3.org/2000/svg">
    <!-- Drop shadow effect -->
    <filter id="float-shadow" x="-10%" y="-10%" width="120%" height="130%">
      <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="#f43f5e" flood-opacity="0.15"/>
    </filter>

    <!-- Card -->
    <rect x="8" y="34" width="244" height="126" rx="8" fill="white" stroke="#f3f4f6" stroke-width="2" filter="url(#float-shadow)"/>

    <!-- Floating badge (overlaps top of card) -->
    <circle cx="202" cy="30" r="30" fill="#f43f5e"/>
    <rect x="186" y="18" width="32" height="8" rx="3" fill="white" opacity="0.85"/>
    <rect x="190" y="30" width="24" height="6" rx="3" fill="white" opacity="0.65"/>

    <!-- Product image -->
    <rect x="16" y="46" width="46" height="46" rx="4" fill="#e5e7eb"/>
    <circle cx="30" cy="57" r="4.5" fill="#d1d5db"/>
    <line x1="16" y1="80" x2="30" y2="68" stroke="#d1d5db" stroke-width="1.5"/>
    <line x1="30" y1="68" x2="40" y2="75" stroke="#d1d5db" stroke-width="1.5"/>
    <line x1="40" y1="75" x2="52" y2="66" stroke="#d1d5db" stroke-width="1.5"/>
    <line x1="52" y1="66" x2="62" y2="80" stroke="#d1d5db" stroke-width="1.5"/>

    <!-- Text lines -->
    <rect x="72" y="50" width="102" height="8" rx="3" fill="#d1d5db"/>
    <rect x="72" y="64" width="82" height="6" rx="3" fill="#e5e7eb"/>

    <!-- Price + orig price -->
    <rect x="72" y="76" width="56" height="8" rx="3" fill="#ffe4e6"/>
    <rect x="136" y="78" width="40" height="5" rx="3" fill="#fecdd3"/>
    <!-- Strikethrough -->
    <line x1="136" y1="80.5" x2="176" y2="80.5" stroke="#fca5a5" stroke-width="1"/>

    <!-- CTA button -->
    <rect x="16" y="108" width="234" height="30" rx="5" fill="#f43f5e"/>
    <rect x="88" y="117" width="84" height="8" rx="3" fill="white" opacity="0.8"/>
  </svg>
</template>
