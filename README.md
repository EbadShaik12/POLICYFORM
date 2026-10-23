# PolicyFlow — Travel Insurance Policy Management Platform

PolicyFlow is an enterprise-grade travel insurance policy request and lifecycle management platform built with **Laravel 12**, **React 19**, **WebGL 2 (OGL)**, and **Google Gemini AI**. 

The platform enables users, agents, and underwriting teams to configure travel parameters, dynamically calculate deterministic premiums, submit policy requests with duplicate protection, track policy status in real-time, explore covered destinations via an interactive WebGL liquid lens carousel, and converse freely with **Maya**, an intelligent AI voice and chat companion.

---

## Table of Contents
- [Technology Stack](#technology-stack)
- [Key Features](#key-features)
  - [1. Maya — Full Conversational AI & Voice Assistant](#1-maya--full-conversational-ai--voice-assistant)
  - [2. React Bits WebGL FlexCarousel](#2-react-bits-webgl-flexcarousel)
  - [3. Deterministic Premium Calculation Engine](#3-deterministic-premium-calculation-engine)
  - [4. Numeric 6-Digit Application & Agent IDs](#4-numeric-6-digit-application--agent-ids)
  - [5. Policy Lifecycle & Insurer Webhook Simulator](#5-policy-lifecycle--insurer-webhook-simulator)
  - [6. Idempotency & Duplicate Prevention](#6-idempotency--duplicate-prevention)
- [Pricing Rules & Multiplier Matrix](#pricing-rules--multiplier-matrix)
- [Architecture & Directory Structure](#architecture--directory-structure)
- [REST API Reference](#rest-api-reference)
- [Setup & Installation](#setup--installation)
- [Running Automated Tests](#running-automated-tests)
- [Database Migration Guide (SQLite, MySQL, PostgreSQL)](#database-migration-guide)
- [Disclaimer](#disclaimer)

---

## Technology Stack

- **Backend Framework:** Laravel 12.x (PHP 8.2+)
- **Frontend & Templating:** Laravel Blade, Semantic HTML5, Custom Vanilla CSS3 Design System
- **Interactive UI Components:**
  - **React Bits `<FlexCarousel />`:** React 19 + OGL (WebGL 2) with custom GLSL shaders for liquid refraction, chromatic dispersion, and inertia physics.
  - **React Bits Pro Shatter Type:** Interactive glass-shard kinetic physics headline.
- **Artificial Intelligence:** Google Gemini API (`gemini-3.5-flash-lite`, `gemini-3.5-flash`, `gemini-3.1-flash-lite`)
- **Speech Technologies:** Web Speech API (SpeechRecognition for voice input, SpeechSynthesis for vocal audio output)
- **Database:** SQLite (default local development) with zero-code-change compatibility for MySQL 8+ and PostgreSQL
- **Asset Bundler:** Vite 7 with pre-bundled production assets in `public/build`
- **Testing:** PHPUnit / Pest Test Suite with 27 automated tests (93 assertions)

---

## Key Features

### 1. Maya — Full Conversational AI & Voice Assistant
Maya is an intelligent, versatile, human-like AI companion integrated directly into the PolicyFlow interface:
- **Talks About Everything:** Powered by Google Gemini (`gemini-3.5-flash-lite`). Maya is not restricted to rigid canned answers; she can converse knowledgeably on **any topic**—astronomy, science, coding, philosophy, creative writing, poetry, culinary recipes, travel recommendations, life advice, and casual banter.
- **Deep Insurance Expertise:** Maya is thoroughly instructed on PolicyFlow's pricing structure, plans (Basic ₹12/day, Plus ₹20/day, Premium ₹30/day), multipliers, coverage limits, and underwriting workflows.
- **Real-Time Policy Tracking:** Mentioning a policy ID (e.g. `track policy #1` or `check 748291`) automatically queries the SQLite database and returns real-time policy data (customer name, trip dates, status, premium, and policy number).
- **Dual-Mode Voice Interface:**
  - **Microphone Voice Input (Speech-to-Text):** Speak directly to Maya hands-free.
  - **Vocal Output (Text-to-Speech):** Maya speaks her answers back in a natural conversational tone, with markdown and punctuation cleaned for smooth audio narration.
  - **Mute / Voice Toggle:** Users can mute or unmute Maya's voice at any time.
- **Offline & Test Fallback:** If the Gemini API is unreachable or during automated unit testing, a deterministic local conversation engine handles greetings, plan inquiries, pricing formulas, and fallback conversations gracefully.

### 2. React Bits WebGL FlexCarousel
An interactive 3D WebGL 2 component showcasing covered destinations worldwide:
- **OGL GPU Shader Rendering:** High-performance WebGL 2 renderer with custom vertex and fragment shaders for real-time liquid glass refraction and chromatic light splitting.
- **4 Interactive Glass Presets:**
  - 💧 **Liquid Glass:** High tilt with fluid dispersion effect.
  - 🎀 **Flowing Ribbon:** Symmetrical twist with smooth wave bend.
  - 🌀 **Vortex Tilt:** Dramatic 30° tilt with high bend curvature.
  - 🏛️ **Arch Rise:** Upward arch curve lifting both sides of the carousel row.
- **Interactive Gestures:** Drag-to-scroll with spring momentum, mouse wheel navigation, and `focusOnClick` card zoom.
- **Destination-to-Calculator Integration:** Selecting any covered destination (e.g., Swiss Alps, Tokyo, Santorini, Goa, Paris, Bali, Manali, New York, Kyoto, Reykjavik) dynamically populates the Premium Calculator and automatically sets the destination tier (`DOMESTIC` vs `INTERNATIONAL`).

### 3. Deterministic Premium Calculation Engine
- **Integer Cents Precision:** All internal arithmetic uses integer cents to eliminate floating-point rounding errors.
- **Debounced Live Recalculation:** Instant recalculation as the user modifies dates, age, destination, plan tier, or sum insured.
- **Validation:** Strict verification ensuring trip start is in the future and trip end is on or after trip start.

### 4. Numeric 6-Digit Application & Agent IDs
- **Simple & Memorable:** Uses clean, 6-digit numeric IDs (e.g. Application ID: `748291`, Agent ID: `1001`) without confusing alphanumeric strings, allowing customers to easily memorize and track their requests over phone or chat.
- **Full Tracking Support:** Both the on-page tracker and Maya recognize 6-digit Application IDs as well as internal database IDs.

### 5. Policy Lifecycle & Insurer Webhook Simulator
- **Status Lifecycle:** `VALIDATED` &rarr; `PENDING` (Submitted to Insurer) &rarr; `ISSUED` (Approved) or `FAILED` (Rejected).
- **Interactive Webhook Panel:** A dedicated developer testing card lets underwriters simulate insurer approval or rejection callbacks in real-time.
- **Real-Time Visual Badges:** Color-coded status pills with timestamps, provider references, and issued policy numbers.

### 6. Idempotency & Duplicate Prevention
- **Unique Composite Key:** Enforced by database constraint on `[agent_id, client_request_id]`.
- **Safe Re-submissions:** Submitting an existing request returns `200 OK` with the existing record rather than creating a duplicate or throwing an error.
- **State Immutability:** Once a policy reaches `ISSUED`, subsequent webhook callbacks or submissions cannot overwrite the policy number or revert its status.

---

## Pricing Rules & Multiplier Matrix

Premiums are calculated deterministically over inclusive travel days ($\text{Days} = \text{TripEnd} - \text{TripStart} + 1$, minimum 1):

$$\text{Premium (Cents)} = \text{round}\left( \frac{\text{Base Rate} \times \text{Days} \times \text{Age Multiplier} \times \text{Dest Multiplier} \times \text{Cov Multiplier}}{10,000} \right)$$
$$\text{Final Premium} = \frac{\text{Premium (Cents)}}{100}$$

### 1. Base Daily Rates
| Plan Code | Base Daily Rate | Target Travelers | Key Inclusions |
| :--- | :--- | :--- | :--- |
| **BASIC** | ₹12 / day | Budget & Domestic | Essential emergency medical, flight delay assistance |
| **PLUS** | ₹20 / day | Families & Holidaymakers | High-limit hospitalization, baggage loss/delay, trip curtailment |
| **PREMIUM** | ₹30 / day | Adventure & Global Trips | Max ₹500k cover, full cancellation, worldwide evacuation, winter sports |

### 2. Age Multipliers (Calculated at Trip Start Date)
| Age Range | Multiplier | Applied When |
| :--- | :--- | :--- |
| **0–17 years** | 1.10x | Minor / Youth cover |
| **18–60 years** | 1.00x | Standard adult rate |
| **61–70 years** | 1.50x | Senior traveler tier |
| **71+ years** | 2.00x | Elderly traveler tier |

### 3. Destination Multipliers
| Destination | Multiplier |
| :--- | :--- |
| **DOMESTIC** | 1.00x |
| **INTERNATIONAL** | 1.80x |

### 4. Coverage Multipliers (Sum Insured)
| Sum Insured | Multiplier |
| :--- | :--- |
| **₹100,000** | 1.00x |
| **₹300,000** | 1.25x |
| **₹500,000** | 1.50x |

---

## Architecture & Directory Structure

```
PolicyFlow/
├── app/
│   ├── Http/Controllers/
│   │   ├── ChatbotController.php        # AI voice & chat assistant endpoint
│   │   ├── Controller.php               # Base controller
│   │   └── PolicyRequestController.php  # Policy REST API & webhook handler
│   ├── Models/
│   │   └── PolicyRequest.php            # Eloquent model with casts & scopes
│   └── Services/
│       ├── ChatbotAssistant.php         # Gemini AI integration, prompt & offline engine
│       └── PremiumCalculator.php        # Deterministic multiplier arithmetic service
├── database/
│   ├── migrations/
│   │   └── 2026_10_23_155227_create_policy_requests_table.php
│   └── database.sqlite                  # SQLite database
├── public/
│   ├── build/                           # Pre-bundled Vite production assets
│   │   ├── assets/
│   │   └── manifest.json
│   ├── css/
│   │   ├── policyflow.css               # Comprehensive custom design system
│   │   └── shatter-type.css             # Glass shard headline animation styles
│   ├── js/
│   │   ├── policyflow.js                # Core frontend form, tracker & webhook simulation
│   │   ├── policyflow-chat.js           # Maya chat & Web Speech API integration
│   │   └── shatter-type.js              # Kinetic physics headline engine
│   └── index.php
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── FlexCarousel.jsx         # WebGL 2 / OGL Liquid Glass Carousel component
│   │   │   └── FlexCarousel.css         # Themed carousel styles
│   │   ├── flex-carousel-init.jsx       # Destination dataset & mount controller
│   │   └── app.js
│   └── views/
│       └── welcome.blade.php            # Homepage with all navigation, forms, and widgets
├── routes/
│   ├── api.php                          # REST API & Chatbot routes
│   └── web.php                          # Homepage route
├── tests/
│   ├── Feature/
│   │   ├── ChatbotTest.php              # Maya AI, voice & general conversational tests
│   │   └── PolicyRequestApiTest.php     # Comprehensive API lifecycle & validation tests
│   └── Unit/
│       └── PremiumCalculatorTest.php    # Multiplier combination unit tests
├── .env.example                         # Environment configuration template
├── package.json                         # Node dependencies (ogl, react, vite)
├── vite.config.js                       # Vite asset bundling configuration
└── composer.json                        # PHP dependencies (Laravel 12, Sanctum)
```

---

## REST API Reference

### 1. Calculate Estimated Premium
* **Route:** `POST /api/calculate-premium`
* **Request:**
  ```json
  {
    "date_of_birth": "1995-06-15",
    "destination": "INTERNATIONAL",
    "trip_start": "2026-11-01",
    "trip_end": "2026-11-10",
    "plan_code": "PLUS",
    "sum_insured": 300000
  }
  ```
* **Response (`200 OK`):**
  ```json
  {
    "premium": 450.00,
    "days": 10,
    "age": 31,
    "base_rate": 20,
    "age_multiplier": 1.0,
    "destination_multiplier": 1.8,
    "coverage_multiplier": 1.25
  }
  ```

### 2. Create Policy Request
* **Route:** `POST /api/policy-requests`
* **Status Codes:**
  * `201 Created`: Successfully created (`status: VALIDATED`).
  * `200 OK`: Duplicate request matched by `agent_id` and `client_request_id` (returns existing record).
  * `422 Unprocessable Entity`: Validation failure.
* **Request:**
  ```json
  {
    "client_request_id": "748291",
    "agent_id": "1001",
    "customer_name": "Jane Doe",
    "date_of_birth": "1992-04-10",
    "destination": "DOMESTIC",
    "trip_start": "2026-12-01",
    "trip_end": "2026-12-05",
    "plan_code": "BASIC",
    "sum_insured": 100000
  }
  ```

### 3. Retrieve Policy Request
* **Route:** `GET /api/policy-requests/{id}`
* Accepts either the numeric database `id` or the 6-digit `client_request_id`.

### 4. List Policy Requests
* **Route:** `GET /api/policy-requests`
* **Query Parameters:** `status`, `search`, `page` (paginated 10 per page).

### 5. Submit for Issuance
* **Route:** `POST /api/policy-requests/{id}/submit`
* Transitions status from `VALIDATED` to `PENDING` and assigns a provider reference.

### 6. Process Mock Insurer Callback
* **Route:** `POST /api/mock-insurer/callback`
* **Request:**
  ```json
  {
    "provider_reference": "INS-REF-ABC123XYZ",
    "status": "ISSUED",
    "policy_number": "POL-2026-887766"
  }
  ```

### 7. Maya AI Voice & Chat Assistant
* **Route:** `POST /api/chatbot`
* **Request:**
  ```json
  {
    "message": "Why do stars twinkle, and how does the Plus Plan compare?",
    "history": []
  }
  ```
* **Response (`200 OK`):**
  ```json
  {
    "reply": "Stars twinkle because of atmospheric refraction... Meanwhile, our Plus Plan at ₹20/day offers comprehensive protection...",
    "voice_text": "Stars twinkle because of atmospheric refraction as light travels through shifting air layers. Meanwhile, our Plus Plan...",
    "intent": "gemini_response",
    "quick_actions": ["Tell me more", "Compare Plans", "Calculate Travel Premium", "Track Policy #1"],
    "policy_data": null
  }
  ```

---

## Setup & Installation

### Prerequisites
- PHP 8.2 or higher (with `pdo_sqlite`, `mbstring`, and `curl` extensions)
- Composer 2.x
- Node.js 18+ and npm (optional for modifying frontend JS; production assets are already pre-bundled)

### Step-by-Step Quickstart

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/EbadShaik12/POLICYFORM.git
   cd POLICYFORM
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set Up Your Gemini API Key (Optional for Maya AI):**
   In `.env`, set your Gemini key:
   ```dotenv
   GEMINI_API_KEY=your_gemini_api_key_here
   GEMINI_MODEL=gemini-3.5-flash-lite
   ```
   *(Note: If no API key is provided, Maya automatically falls back to the deterministic local offline assistant engine).*

5. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```

6. **(Optional) Build Frontend Assets:**
   Production assets are already compiled and committed in `public/build`. If you modify JSX or CSS files, rebuild with:
   ```bash
   npm install
   npm run build
   ```

7. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your web browser.

---

## Running Automated Tests

Run the full PHPUnit / Pest automated test suite:
```bash
php artisan test
```

### Test Coverage Highlights:
- **`Tests\Unit\PremiumCalculatorTest`:** Verifies base rates, all age multiplier ranges (youth, standard, senior, elderly), destination multipliers, coverage multipliers, and invalid plan exceptions.
- **`Tests\Feature\PolicyRequestApiTest`:** Verifies input validation, policy creation, idempotency on duplicate submission, insurer submission, webhook approval/rejection callbacks, immutability of issued policies, and 6-digit numeric IDs.
- **`Tests\Feature\ChatbotTest`:** Verifies greetings, plan inquiries, formula explanations, database tracking by ID, Gemini API integration, and general AI conversational capabilities on non-prompted topics.

**Expected Test Output:**
```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Unit\PremiumCalculatorTest (6 tests)
PASS  Tests\Feature\ChatbotTest (7 tests)
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\PolicyRequestApiTest (12 tests)

Tests:    27 passed (93 assertions)
Duration: 1.67s
```

---

## Database Migration Guide (SQLite to MySQL / PostgreSQL)

The database schema is fully agnostic and portable across all major SQL databases:

### For MySQL 8+:
Update `.env`:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=policyflow
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
Run migrations:
```bash
php artisan migrate:fresh
```

### For PostgreSQL:
Update `.env`:
```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=policyflow
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
Run migrations:
```bash
php artisan migrate:fresh
```

---

## Deploying to Vercel

PolicyFlow is pre-configured for one-click deployment to Vercel using `vercel.json` and the serverless `api/index.php` gateway.

### Steps:
1. Push your repository to GitHub.
2. In the [Vercel Dashboard](https://vercel.com/dashboard), click **Add New** &rarr; **Project** and import your repository.
3. In the **Environment Variables** section, add:
   - `APP_KEY`: Generate using `php artisan key:generate --show` (or copy from `.env`).
   - `GEMINI_API_KEY` *(Optional)*: Your Google Gemini API key for Maya AI.
4. Click **Deploy**. Vercel will install dependencies without peer conflicts (via `.npmrc`), build frontend assets with Vite, and launch the serverless application.

---

## Disclaimer

PolicyFlow is an assessment project created solely for demonstration purposes. It is not an authorized financial distributor, insurance intermediary, or underwriter. All insurance plans, rates, policy numbers, and quotes are strictly fictional.

