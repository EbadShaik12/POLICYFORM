<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PolicyFlow | Professional Travel Insurance Policy Management</title>
    <meta name="description" content="PolicyFlow makes travel insurance policy management effortless. Calculate premiums accurately, submit policy requests, and track real-time issuance status.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/policyflow.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/shatter-type.css') }}?v={{ time() }}">
    @vite(['resources/js/flex-carousel-init.jsx'])
</head>
<body>

    <!-- A. Navigation Bar -->
    <header class="navbar">
        <div class="container nav-container">
            <a href="#home" class="brand-logo" id="navBrandLogo">
                <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                <span>PolicyFlow</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <ul class="nav-links" id="navLinks">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#howItWorks" class="nav-link">How It Works</a></li>
                <li><a href="#plans" class="nav-link">Insurance Plans</a></li>
                <li><a href="#destinations" class="nav-link">Destinations</a></li>
                <li><a href="#trackingSection" class="nav-link">Track Request</a></li>
            </ul>

            <div class="nav-cta">
                <a href="#calculatorSection" class="btn btn-primary btn-sm">Get Started</a>
            </div>
        </div>
    </header>

    <main>
        <!-- B. Hero Section -->
        <section id="home" class="hero-section">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <span class="section-tag">Travel Insurance Platform</span>
                        
                        <!-- Shatter Type Interactive Headline Component -->
                        <div class="shatter-type-wrapper" id="heroShatterWrapper" title="Hover to crack glass • Click to shatter">
                            <div class="shatter-type-container">
                                <h1 class="hero-title shatter-type-text" id="shatterHeadline">Travel with confidence. We've simplified the rest.</h1>
                                <canvas class="shatter-canvas" id="shatterCanvas"></canvas>
                                <svg class="shatter-crack-svg" id="shatterCrackSvg"></svg>
                                <div class="shatter-shimmer"></div>
                            </div>
                        </div>

                        <div class="shatter-hint-pill" id="shatterHintPill" style="cursor:pointer;" title="Click to trigger glass shatter effect">
                            <svg style="width:12px; height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>Hover to crack &bull; Click to shatter</span>
                        </div>

                        <p class="hero-desc">
                            Understand your travel insurance options, calculate your premium, and follow your policy request from one simple place.
                        </p>
                        <div class="hero-actions">
                            <a href="#calculatorSection" class="btn btn-primary btn-lg">Get Started</a>
                            <a href="#howItWorks" class="btn btn-secondary btn-lg">How It Works</a>
                        </div>
                    </div>

                    <!-- Hero Visual Card -->
                    <div class="hero-visual">
                        <div class="hero-preview-card">
                            <div class="preview-badge-row">
                                <span class="preview-badge">
                                    <svg style="width:14px; height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Real-Time Rate Engine
                                </span>
                                <span class="preview-status">Status: Validated</span>
                            </div>

                            <div class="preview-item">
                                <span class="label">Sample Coverage</span>
                                <span class="val">₹300,000 Sum Insured</span>
                            </div>
                            <div class="preview-item">
                                <span class="label">Selected Tier</span>
                                <span class="val">Plus Plan (₹20/day)</span>
                            </div>
                            <div class="preview-item">
                                <span class="label">Trip Type</span>
                                <span class="val">Domestic & International</span>
                            </div>

                            <div class="preview-price-box">
                                <div class="price-label">Verified Fair Pricing</div>
                                <div class="price-amount">Transparent Rates</div>
                                <div class="price-note">Precise integer-cent calculation, zero hidden fees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- C. Trust Highlights Section -->
        <section class="highlights-section">
            <div class="container">
                <div class="highlights-grid">
                    <div class="highlight-card">
                        <div class="highlight-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <line x1="8" y1="6" x2="16" y2="6"></line>
                                <line x1="8" y1="10" x2="16" y2="10"></line>
                                <line x1="8" y1="14" x2="16" y2="14"></line>
                            </svg>
                        </div>
                        <div class="highlight-info">
                            <h3>Transparent Premium Calculation</h3>
                            <p>Clear, deterministic pricing rules based on travel duration, plan type, age, and coverage amount.</p>
                        </div>
                    </div>

                    <div class="highlight-card">
                        <div class="highlight-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <div class="highlight-info">
                            <h3>Easy Request Tracking</h3>
                            <p>Instantly check any policy request status anytime using your database ID or client reference identifier.</p>
                        </div>
                    </div>

                    <div class="highlight-card">
                        <div class="highlight-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="highlight-info">
                            <h3>Clear Policy Status Updates</h3>
                            <p>Follow transparent lifecycle changes from validation to pending insurer submission and confirmed issuance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- D. How It Works Section -->
        <section id="howItWorks" class="section-py">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Simple Process</span>
                    <h2 class="section-title">How PolicyFlow Works</h2>
                    <p class="section-subtitle">
                        From entering trip details to receiving your confirmed policy number, every step is automated and transparent.
                    </p>
                </div>

                <div class="how-it-works-grid">
                    <div class="step-card">
                        <div class="step-num-icon">
                            <span class="step-number">1</span>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </div>
                        <h3>Enter Trip Details</h3>
                        <p>Provide customer information, date of birth, travel dates, and intended destination.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-num-icon">
                            <span class="step-number">2</span>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                            </svg>
                        </div>
                        <h3>Calculate Premium</h3>
                        <p>Calculate the verified price dynamically using defined daily rates and official multiplier formulas.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-num-icon">
                            <span class="step-number">3</span>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </div>
                        <h3>Submit Request</h3>
                        <p>Save and process the request with built-in protection against accidental duplicate submissions.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-num-icon">
                            <span class="step-number">4</span>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Track Status</h3>
                        <p>See whether your policy request is received, validated, pending underwriting, issued, or failed.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- E. Insurance Plans Section -->
        <section id="plans" class="section-py" style="background-color: var(--color-white); border-top: 1px solid var(--color-light-border);">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Assessment Plans</span>
                    <h2 class="section-title">Insurance Plans Tailored to Your Journey</h2>
                    <p class="section-subtitle">
                        Select a base plan tier. Final premiums are calculated based on your age, destination, coverage amount, and trip duration.
                    </p>
                </div>

                <div class="plans-grid">
                    <!-- Plan 1: Basic -->
                    <div class="plan-card">
                        <h3 class="plan-name">Basic Plan</h3>
                        <p class="plan-tagline">Essential travel protection for short domestic or low-risk journeys.</p>
                        
                        <div class="plan-price-wrap">
                            <span class="plan-rate">₹12</span>
                            <span class="plan-unit">/ travel day</span>
                        </div>
                        <div class="plan-multiplier-note">Before applicable age, destination & coverage multipliers</div>

                        <ul class="plan-features">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Emergency Medical Expenses
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Flight Delay Compensation
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                24/7 Assistance Helpline
                            </li>
                        </ul>

                        <button type="button" class="btn btn-secondary btn-select-plan" data-plan="BASIC">Select Basic Plan</button>
                    </div>

                    <!-- Plan 2: Plus -->
                    <div class="plan-card featured">
                        <span class="plan-ribbon">Most Popular</span>
                        <h3 class="plan-name">Plus Plan</h3>
                        <p class="plan-tagline">Comprehensive protection including baggage delays and higher medical limits.</p>
                        
                        <div class="plan-price-wrap">
                            <span class="plan-rate">₹20</span>
                            <span class="plan-unit">/ travel day</span>
                        </div>
                        <div class="plan-multiplier-note">Before applicable age, destination & coverage multipliers</div>

                        <ul class="plan-features">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                High-Limit Medical & Hospitalization
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Checked Baggage Loss & Delay
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Trip Curtailment & Interruptions
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Dedicated Claims Concierge
                            </li>
                        </ul>

                        <button type="button" class="btn btn-primary btn-select-plan" data-plan="PLUS">Select Plus Plan</button>
                    </div>

                    <!-- Plan 3: Premium -->
                    <div class="plan-card">
                        <h3 class="plan-name">Premium Plan</h3>
                        <p class="plan-tagline">Maximum protection covering trip cancellation, high coverage & adventure sports.</p>
                        
                        <div class="plan-price-wrap">
                            <span class="plan-rate">₹30</span>
                            <span class="plan-unit">/ travel day</span>
                        </div>
                        <div class="plan-multiplier-note">Before applicable age, destination & coverage multipliers</div>

                        <ul class="plan-features">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Maximum ₹500,000 Coverage Support
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Comprehensive Trip Cancellation
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                International Emergency Evacuation
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Adventure Activity Cover
                            </li>
                        </ul>

                        <button type="button" class="btn btn-secondary btn-select-plan" data-plan="PREMIUM">Select Premium Plan</button>
                    </div>
                </div>

                <div class="plans-disclaimer">
                    <strong>Note:</strong> These are fictional assessment plans created for demonstration purposes, not actual insurance policies. Actual premiums are determined by the pricing formulas specified in the system.
                </div>
            </div>
        </section>

        <!-- F. Worldwide Covered Destinations (Interactive FlexCarousel Component) -->
        <section id="destinations" class="destinations-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Worldwide Protection</span>
                    <h2 class="section-title">Destinations Covered Worldwide</h2>
                    <p class="section-subtitle">
                        Explore covered domestic and international travel destinations powered by our real-time rate engine and interactive WebGL liquid lens technology.
                    </p>
                </div>

                <!-- React Bits FlexCarousel Mount Container -->
                <div id="flex-carousel-root"></div>
            </div>
        </section>

        <!-- G. Premium Calculator & Request Form Section -->
        <section id="calculatorSection" class="section-py calc-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Interactive Calculation</span>
                    <h2 class="section-title">Calculate Premium & Submit Policy Request</h2>
                    <p class="section-subtitle">
                        Complete your details below to estimate your exact premium and submit your policy request for validation.
                    </p>
                </div>

                <div class="calc-grid">
                    <!-- Left: Form -->
                    <div class="card">
                        <h3 class="card-title">Policy Request Details</h3>
                        <p class="card-desc">All required fields are validated in real-time according to official underwriting constraints.</p>

                        <form id="policyRequestForm" novalidate>
                            <div class="form-grid">
                                <!-- Application / Client Request ID -->
                                <div class="form-group col-span-2">
                                    <label for="client_request_id" class="form-label">
                                        Application ID *
                                        <span class="label-hint">Simple 6-digit memorable number</span>
                                    </label>
                                    <div class="input-with-button">
                                        <input type="text" id="client_request_id" name="client_request_id" class="form-control" value="{{ rand(100000, 999999) }}" placeholder="e.g. 748291" inputmode="numeric" pattern="[0-9]*" maxlength="10" required>
                                        <button type="button" id="btnGenerateId" class="btn btn-secondary btn-sm" title="Generate New 6-Digit ID">Generate ID</button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Agent ID -->
                                <div class="form-group">
                                    <label for="agent_id" class="form-label">
                                        Agent ID *
                                        <span class="label-hint">Numeric</span>
                                    </label>
                                    <input type="text" id="agent_id" name="agent_id" class="form-control" value="1001" placeholder="e.g. 1001" inputmode="numeric" pattern="[0-9]*" maxlength="10" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Customer Name -->
                                <div class="form-group">
                                    <label for="customer_name" class="form-label">Customer Name *</label>
                                    <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="e.g. John Doe" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Date of Birth -->
                                <div class="form-group">
                                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Destination -->
                                <div class="form-group">
                                    <label for="destination" class="form-label">Destination *</label>
                                    <select id="destination" name="destination" class="form-control" required>
                                        <option value="DOMESTIC">Domestic (1.00x multiplier)</option>
                                        <option value="INTERNATIONAL">International (1.80x multiplier)</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Trip Start Date -->
                                <div class="form-group">
                                    <label for="trip_start" class="form-label">Trip Start Date *</label>
                                    <input type="date" id="trip_start" name="trip_start" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Trip End Date -->
                                <div class="form-group">
                                    <label for="trip_end" class="form-label">Trip End Date *</label>
                                    <input type="date" id="trip_end" name="trip_end" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Plan Code -->
                                <div class="form-group">
                                    <label for="plan_code" class="form-label">Plan Tier *</label>
                                    <select id="plan_code" name="plan_code" class="form-control" required>
                                        <option value="BASIC">Basic (₹12/day base)</option>
                                        <option value="PLUS" selected>Plus (₹20/day base)</option>
                                        <option value="PREMIUM">Premium (₹30/day base)</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Sum Insured -->
                                <div class="form-group">
                                    <label for="sum_insured" class="form-label">Coverage Amount *</label>
                                    <select id="sum_insured" name="sum_insured" class="form-control" required>
                                        <option value="100000">₹100,000 (1.00x multiplier)</option>
                                        <option value="300000" selected>₹300,000 (1.25x multiplier)</option>
                                        <option value="500000">₹500,000 (1.50x multiplier)</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Form Actions -->
                                <div class="form-actions">
                                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg" style="flex-grow:1;">
                                        Submit Policy Request
                                    </button>
                                    <button type="button" id="btnCalculate" class="btn btn-secondary btn-lg">
                                        Recalculate Estimate
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="formAlert" class="alert-box" role="alert"></div>
                    </div>

                    <!-- Right: Live Estimate Breakdown -->
                    <div class="estimate-card">
                        <div class="estimate-header">
                            <h3>Estimated Premium</h3>
                            <p style="font-size:0.85rem; color:var(--color-muted-text);">Deterministic Pricing Engine</p>
                        </div>

                        <div class="estimate-price-banner">
                            <div class="price-tag">Total Estimated Premium</div>
                            <div class="big-price" id="estBigPrice">₹0.00</div>
                            <div class="price-note">All taxes and applicable multipliers included</div>
                        </div>

                        <div class="breakdown-list">
                            <div class="breakdown-row">
                                <span>Travel Duration (Inclusive)</span>
                                <strong id="estDays">0 days</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Base Daily Rate</span>
                                <strong id="estBaseRate">₹20/day</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Age Multiplier</span>
                                <strong id="estAgeMult">1.00x</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Destination Multiplier</span>
                                <strong id="estDestMult">1.00x</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Coverage Multiplier</span>
                                <strong id="estCovMult">1.25x</strong>
                            </div>
                            <div class="breakdown-row total-row">
                                <span>Calculation Method</span>
                                <span>Integer Cents (Decimal-Safe)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- G. Request Tracking Section -->
        <section id="trackingSection" class="section-py track-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Status Inquiry</span>
                    <h2 class="section-title">Track Policy Request</h2>
                    <p class="section-subtitle">
                        Enter your Policy Request ID or Client Reference ID to view live verification, underwriting, and issuance status.
                    </p>
                </div>

                <div class="track-box">
                    <form id="trackForm" class="track-search-form" novalidate>
                        <input type="text" id="trackIdInput" class="form-control" placeholder="Enter Application ID (e.g. 748291) or Database ID (e.g. 1)" required inputmode="numeric">
                        <button type="submit" id="btnTrack" class="btn btn-primary" style="padding: 12px 28px;">
                            Track Request
                        </button>
                    </form>

                    <div id="trackAlert" class="alert-box" role="alert"></div>

                    <!-- Tracking Result Card -->
                    <div id="trackResultCard" class="tracking-result"></div>

                    <!-- Quick Recent Requests Table -->
                    <div class="recent-requests-box">
                        <h4>
                            <span>Recent Policy Requests</span>
                            <span style="font-size:0.75rem; font-weight:normal; color:var(--color-muted-text);">Click any row to track</span>
                        </h4>
                        <div class="recent-table-wrap">
                            <table class="recent-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client Reference</th>
                                        <th>Customer</th>
                                        <th>Premium</th>
                                        <th>Status</th>
                                        <th>Policy Number</th>
                                    </tr>
                                </thead>
                                <tbody id="recentRequestsList">
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:14px; color:var(--color-muted-text);">
                                            Loading recent requests...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- H. Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="brand-logo">
                        <svg class="brand-icon" style="color:var(--color-white);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                        <span>PolicyFlow</span>
                    </div>
                    <p>
                        A streamlined travel insurance policy management platform designed to calculate accurate premiums, eliminate duplicate requests, and track status transparently.
                    </p>
                </div>

                <div class="footer-links">
                    <h4>Platform Navigation</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#howItWorks">How It Works</a></li>
                        <li><a href="#plans">Insurance Plans</a></li>
                        <li><a href="#calculatorSection">Premium Calculator</a></li>
                        <li><a href="#trackingSection">Track Request</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>API Endpoints</h4>
                    <ul>
                        <li><a href="/api/policy-requests" target="_blank">GET /api/policy-requests</a></li>
                        <li><span style="font-size:0.85rem; color:#8CA4C2;">POST /api/policy-requests</span></li>
                        <li><span style="font-size:0.85rem; color:#8CA4C2;">POST /api/calculate-premium</span></li>
                        <li><span style="font-size:0.85rem; color:#8CA4C2;">POST /api/policy-requests/{id}/submit</span></li>
                        <li><span style="font-size:0.85rem; color:#8CA4C2;">POST /api/mock-insurer/callback</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-disclaimer">
                    <strong>Fictional Demonstration Disclaimer:</strong> PolicyFlow is a prototype application created strictly for technical assessment and portfolio demonstration purposes. It is not a licensed insurance company, broker, or financial institution. No actual insurance coverage, financial transactions, or real policies are provided or issued.
                </div>
                <div class="footer-copyright">
                    &copy; 2026 PolicyFlow. Built with Laravel 12, PHP 8.2, Blade, Vanilla CSS, and JavaScript.
                </div>
            </div>
        </div>
    </footer>

    <!-- Maya AI Voice & Chatbot Assistant Widget -->
    <div class="chat-launcher-container">
        <div class="chat-launcher-tooltip">
            <svg style="width:14px; height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" y1="19" x2="12" y2="23"></line>
                <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            Ask or Speak with Maya
        </div>
        <button type="button" class="chat-launcher-btn" id="chatLauncherBtn" aria-label="Open Maya Voice & Chat Assistant">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span class="chat-online-pulse"></span>
        </button>
    </div>

    <!-- Chatbot Window -->
    <div class="chat-widget" id="chatWidget" role="dialog" aria-label="Maya Voice and Chat Assistant">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar">M</div>
                <div>
                    <div class="chat-header-title">Maya — PolicyFlow Assistant</div>
                    <div class="chat-header-subtitle">
                        <span class="chat-status-dot"></span>
                        Voice &amp; Text Assistant
                    </div>
                </div>
            </div>
            <div class="chat-header-actions">
                <button type="button" class="chat-tool-btn active-voice" id="btnToggleVoiceMute" title="Voice assistant speaking: ON">
                    <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    </svg>
                </button>
                <button type="button" class="chat-tool-btn" id="chatCloseBtn" title="Close Assistant">
                    <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <!-- Voice Listening Banner -->
            <div class="voice-listening-banner" id="voiceListeningBanner">
                <div class="listening-text">
                    <span class="chat-status-dot" style="background:#EF4444;"></span>
                    Listening to you... Speak now
                </div>
                <div class="soundwave">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <!-- Welcome Message from Maya -->
            <div class="chat-bubble bot">
                <div>Hello! I'm <strong>Maya</strong>, your conversational AI assistant. 🎙️</div>
                <div style="margin-top:6px;">
                    I can chat with you about <strong>anything</strong>—from science, coding, and travel stories to calculating travel insurance premiums and tracking policies in real-time. Speak with the microphone or type below!
                </div>
                <button type="button" class="bot-speaker-icon" onclick="window.askMaya && window.speechSynthesis && window.speechSynthesis.speak(new SpeechSynthesisUtterance('Hello! I am Maya, your conversational AI assistant. I can chat about anything, or help with travel planning and insurance. You can speak or type to me anytime.'))">
                    <svg style="width:12px; height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    </svg>
                    Listen
                </button>

                <div class="chat-quick-actions" style="margin-top:10px;">
                    <button type="button" class="chip-btn" onclick="window.askMaya('Tell me an amazing fact about the universe')">Universe Fun Fact</button>
                    <button type="button" class="chip-btn" onclick="window.askMaya('Compare Plans')">Compare Plans</button>
                    <button type="button" class="chip-btn" onclick="window.askMaya('Recommend top places to visit in Japan')">Visit Japan</button>
                    <button type="button" class="chip-btn" onclick="window.askMaya('How are premiums calculated?')">How are premiums calculated?</button>
                </div>
            </div>

            <!-- Typing indicator -->
            <div class="typing-indicator" id="typingIndicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Chat Footer -->
        <div class="chat-footer">
            <form id="chatInputForm" class="chat-input-form">
                <input type="text" id="chatInput" class="chat-input" placeholder="Type or speak to Maya..." autocomplete="off">
                <button type="button" class="btn-mic" id="btnVoiceMic" title="Click to speak (Voice Assistant)">
                    <svg style="width:18px; height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <line x1="12" y1="19" x2="12" y2="23"></line>
                        <line x1="8" y1="23" x2="16" y2="23"></line>
                    </svg>
                </button>
                <button type="submit" class="btn-send" id="btnChatSend" title="Send message">
                    <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/policyflow.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/policyflow-chat.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/shatter-type.js') }}?v={{ time() }}"></script>
</body>
</html>

