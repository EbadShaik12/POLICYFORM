import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import FlexCarousel from './components/FlexCarousel';

const TRAVEL_DESTINATIONS = [
  {
    src: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1200&q=80&auto=format&fit=crop',
    alt: 'Alpine mountain lake in Switzerland',
    title: 'Swiss Alps, Switzerland',
    subtitle: 'International • Premium Evacuation & Winter Sports'
  },
  {
    src: 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?w=1200&q=80&auto=format&fit=crop',
    alt: 'Tokyo illuminated city street and tower',
    title: 'Tokyo, Japan',
    subtitle: 'International • Plus Plan Medical & Flight Delay'
  },
  {
    src: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=1200&q=80&auto=format&fit=crop',
    alt: 'Santorini white houses and blue sea',
    title: 'Santorini, Greece',
    subtitle: 'International • High-Limit Medical & Luggage Loss'
  },
  {
    src: 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=1200&q=80&auto=format&fit=crop',
    alt: 'Goa palm trees and beach at sunset',
    title: 'Goa, India',
    subtitle: 'Domestic • Basic & Plus Emergency Assistance'
  },
  {
    src: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=1200&q=80&auto=format&fit=crop',
    alt: 'Eiffel tower in Paris France',
    title: 'Paris, France',
    subtitle: 'International • Hospitalization & Trip Curtailment'
  },
  {
    src: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80&auto=format&fit=crop',
    alt: 'Bali lush tropical temple view',
    title: 'Bali, Indonesia',
    subtitle: 'International • Adventure Sports & 24/7 Support'
  },
  {
    src: 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=1200&q=80&auto=format&fit=crop',
    alt: 'Snow covered Himalayan mountains in Manali',
    title: 'Manali, Himachal Pradesh',
    subtitle: 'Domestic • Essential Medical & Trip Interruption'
  },
  {
    src: 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=1200&q=80&auto=format&fit=crop',
    alt: 'New York City skyline with historic architecture',
    title: 'New York City, USA',
    subtitle: 'International • Maximum ₹500,000 Coverage Tier'
  },
  {
    src: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=1200&q=80&auto=format&fit=crop',
    alt: 'Traditional pagoda in Kyoto Japan',
    title: 'Kyoto, Japan',
    subtitle: 'International • Comprehensive Overseas Protection'
  },
  {
    src: 'https://images.unsplash.com/photo-1504893524553-b855bce32c67?w=1200&q=80&auto=format&fit=crop',
    alt: 'Northern lights over snowy mountains in Iceland',
    title: 'Reykjavik, Iceland',
    subtitle: 'International • Severe Weather & Flight Cancellation'
  }
];

function CarouselDemo() {
  const [preset, setPreset] = useState('liquid');
  const [selectedDestination, setSelectedDestination] = useState(TRAVEL_DESTINATIONS[0]);

  const presets = [
    { id: 'liquid', label: 'Liquid Glass' },
    { id: 'ribbon', label: 'Flowing Ribbon' },
    { id: 'vortex', label: 'Vortex Tilt' },
    { id: 'arch', label: 'Arch Rise' }
  ];

  const handleSelect = (index, item) => {
    setSelectedDestination(item);
  };

  const handleChange = (index, item) => {
    setSelectedDestination(item);
  };

  const handleApplyToCalculator = (dest) => {
    const isDomestic = dest.subtitle.includes('Domestic');
    const destSelect = document.getElementById('destination');
    if (destSelect) {
      destSelect.value = isDomestic ? 'DOMESTIC' : 'INTERNATIONAL';
      destSelect.dispatchEvent(new Event('change'));
    }
    const calcSec = document.getElementById('calculatorSection');
    if (calcSec) {
      calcSec.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <div className="flex-carousel-wrapper">
      {/* Interactive Controls Bar */}
      <div className="flex-carousel-controls">
        <div className="flex-carousel-preset-group">
          <span className="preset-label">Glass Refraction Preset:</span>
          {presets.map(p => (
            <button
              key={p.id}
              type="button"
              className={`preset-btn ${preset === p.id ? 'active' : ''}`}
              onClick={() => setPreset(p.id)}
            >
              {p.label}
            </button>
          ))}
        </div>

        <div className="flex-carousel-hint">
          <span>Drag to scroll &bull; Click card to zoom &bull; Mouse wheel supported</span>
        </div>
      </div>

      {/* WebGL OGL Flex Carousel */}
      <div style={{ width: '100%', height: '520px', position: 'relative' }}>
        <FlexCarousel
          items={TRAVEL_DESTINATIONS}
          preset={preset}
          intro="rise"
          cardHeight={0.52}
          gap={16}
          radius={14}
          squeeze={0.22}
          focusOnClick={true}
          captions={true}
          onChange={handleChange}
          onSelect={handleSelect}
        />
      </div>

      {/* Selected Destination CTA Bar */}
      {selectedDestination && (
        <div className="carousel-selected-banner">
          <div className="selected-info">
            <span className="selected-badge">Selected Destination</span>
            <strong className="selected-title">{selectedDestination.title}</strong>
            <span className="selected-sub">{selectedDestination.subtitle}</span>
          </div>
          <button
            type="button"
            className="btn btn-primary btn-sm"
            onClick={() => handleApplyToCalculator(selectedDestination)}
          >
            Calculate Premium for {selectedDestination.title.split(',')[0]} &rarr;
          </button>
        </div>
      )}
    </div>
  );
}

function mountCarousel() {
  const mountPoint = document.getElementById('flex-carousel-root');
  if (mountPoint) {
    const root = createRoot(mountPoint);
    root.render(<CarouselDemo />);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountCarousel);
} else {
  mountCarousel();
}
