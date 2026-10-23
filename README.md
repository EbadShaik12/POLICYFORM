# PolicyFlow — Travel Insurance Policy Management Platform

PolicyFlow is a professional travel insurance request management platform built for insurance distributors and underwriting teams. The application allows users to configure travel parameters, dynamically calculate deterministic premiums, submit policy requests with duplicate prevention, and track policy issuance in real-time.

---

## Table of Contents
- [Technology Stack](#technology-stack)
- [Key Features](#key-features)
- [Pricing Rules and Calculation Engine](#pricing-rules-and-calculation-engine)
- [Architecture & Directory Structure](#architecture--directory-structure)
- [API Reference](#api-reference)
- [Setup & Installation](#setup--installation)
- [Running Automated Tests](#running-automated-tests)
- [Database Migration Guide (SQLite to MySQL / PostgreSQL)](#database-migration-guide)
- [Fictional Demonstration Disclaimer](#fictional-demonstration-disclaimer)

---

## Technology Stack

- **Framework:** Laravel 12.x
- **Language:** PHP 8.2+
- **Frontend:** Laravel Blade, Semantic HTML5, Vanilla CSS3 (Custom Design System), Vanilla JavaScript (ES6+)
- **Database:** SQLite (default local development) with zero-change compatibility for MySQL 8+ or PostgreSQL
- **Testing:** PHPUnit / Pest Test Suite with Feature and Unit tests

---

## Key Features

1. **Deterministic Premium Calculation Engine:**
   - Accurate integer-cents arithmetic preventing floating point rounding discrepancies.
   - Live frontend recalculation debounced over the `/api/calculate-premium` endpoint.
2. **Robust Policy Request Lifecycle:**
   - Status transitions: `RECEIVED` &rarr; `VALIDATED` &rarr; `PENDING` &rarr; `ISSUED` (or `FAILED`).
3. **Idempotency & Duplicate Request Protection:**
   - Enforced by composite database unique constraints on `[agent_id, client_request_id]`.
   - Repeated submissions return HTTP `200 OK` with the existing record rather than creating duplicate policies or erroring.
4. **State Immutability Protection:**
   - Once a policy transitions to `ISSUED`, repeated submission or simulated webhook callbacks cannot overwrite or duplicate the confirmed state.
5. **Interactive UI / UX:**
   - Tailored corporate palette (`#15345B`, `#155EEF`, `#F5F9FF`, `#DCE7F5`, `#526780`).
   - Accessible form controls, real-time field error indicators, and live tracking dashboard.
   - Built-in simulation panel for testing insurer webhook approvals/rejections during assessments.
6. **Maya — AI Voice & Chatbot Assistant:**
   - Real-time conversational human assistance for plan comparisons, pricing explanations, submission guidance, and instant policy status lookup.
   - Dual-mode voice interaction: Speech-to-Text input (Microphone) and Text-to-Speech vocal output using Web Speech synthesis.
   - Interactive quick-action chips and audio re-play controls on bot message bubbles.

---

## Pricing Rules and Calculation Engine

The premium is calculated using deterministic multipliers over inclusive travel days ($TripEnd - TripStart + 1$, minimum 1 day):

$$\text{Premium (Cents)} = \text{round}\left( \frac{\text{Base Rate} \times \text{Days} \times \text{Age Multiplier} \times \text{Dest Multiplier} \times \text{Cov Multiplier}}{10,000} \right)$$
$$\text{Final Premium} = \frac{\text{Premium (Cents)}}{100}$$

### 1. Base Daily Rates
| Plan Code | Base Daily Rate | Description |
| :--- | :--- | :--- |
| **BASIC** | ₹12 / day | Essential medical and delay coverage |
| **PLUS** | ₹20 / day | Comprehensive medical, baggage & curtailment |
| **PREMIUM** | ₹30 / day | Maximum coverage, cancellation & adventure cover |

### 2. Age Multipliers (Calculated at Trip Start)
| Age Range | Multiplier | Calculation |
| :--- | :--- | :--- |
| **0–17 years** | 1.10x | `age <= 17` |
| **18–60 years** | 1.00x | `age <= 60` |
| **61–70 years** | 1.50x | `age <= 70` |
| **71+ years** | 2.00x | `age >= 71` |

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
│   │   └── PolicyRequestController.php  # Handles all REST API requests
│   ├── Models/
│   │   └── PolicyRequest.php            # Eloquent Model with casts and fillables
│   └── Services/
│       └── PremiumCalculator.php        # Pure service for rate formulas & multipliers
├── database/
│   ├── migrations/
│   │   └── 2026_10_23_155227_create_policy_requests_table.php
│   └── database.sqlite
├── public/
│   ├── css/
│   │   └── policyflow.css               # Design system & responsive layout styles
│   └── js/
│       └── policyflow.js                # Vanilla JS for form, calculation & tracking
├── resources/
│   └── views/
│       └── welcome.blade.php            # Complete homepage with all required sections
├── routes/
│   ├── api.php                          # All 6 REST API routes
│   └── web.php                          # Web route serving the Blade view
└── tests/
    ├── Feature/
    │   └── PolicyRequestApiTest.php     # Comprehensive API lifecycle & validation tests
    └── Unit/
        └── PremiumCalculatorTest.php    # Unit tests for multiplier combinations
```

---

## API Reference

### 1. Calculate Estimated Premium
* **Route:** `POST /api/calculate-premium`
* **Payload:**
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
  * `201 Created`: Newly created policy request (`status: VALIDATED`).
  * `200 OK`: Duplicate request matched by `agent_id` and `client_request_id`.
  * `422 Unprocessable Entity`: Field validation failure.
* **Payload:**
  ```json
  {
    "client_request_id": "REQ-1001",
    "agent_id": "AGENT-42",
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
* Accepts either numeric database `id` or string `client_request_id`.
* **Status Codes:** `200 OK` or `404 Not Found`.

### 4. List Policy Requests
* **Route:** `GET /api/policy-requests`
* **Query Parameters:**
  * `status`: Filter by status (`RECEIVED`, `VALIDATED`, `PENDING`, `ISSUED`, `FAILED`).
  * `search`: Filter by customer name, agent ID, reference, or policy number.
  * `page`: Page number for pagination (10 per page).

### 5. Submit for Issuance
* **Route:** `POST /api/policy-requests/{id}/submit`
* Transitions status to `PENDING`, assigns `provider_reference`, and increments `attempt_count`.
* If already `ISSUED`, returns existing policy safely without modifications.

### 6. Process Mock Insurer Callback
* **Route:** `POST /api/mock-insurer/callback`
* **Payload:**
  ```json
  {
    "provider_reference": "INS-REF-ABC123XYZ",
    "status": "ISSUED",
    "policy_number": "POL-2026-887766"
  }
  ```
* **Reliability Rule:** Cannot overwrite an already `ISSUED` policy request.

### 7. AI Voice & Chatbot Assistant
* **Route:** `POST /api/chatbot`
* **Payload:**
  ```json
  {
    "message": "Can you track policy #1?",
    "history": []
  }
  ```
* **Response (`200 OK`):**
  ```json
  {
    "reply": "Great news! Policy request #1 for Jane Smith has been officially ISSUED!...",
    "voice_text": "Great news! Policy number POL-2026-987654 for Jane Smith has been officially issued...",
    "intent": "policy_found",
    "quick_actions": ["Track another policy", "Compare Plans", "How are premiums calculated?"],
    "policy_data": {
      "id": 1,
      "status": "ISSUED",
      "policy_number": "POL-2026-987654"
    }
  }
  ```

---

## Setup & Installation

### Prerequisites
- PHP 8.2 or higher (with `pdo_sqlite` and `mbstring` extensions)
- Composer 2.x

### Quickstart
1. Clone or open the repository:
   ```bash
   cd PolicyFlow
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up the environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Run migrations:
   ```bash
   php artisan migrate
   ```
5. Start the development server:
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your web browser.

---

## Running Automated Tests

Run the full PHPUnit test suite:
```bash
php artisan test
```

To run specific test suites:
```bash
# Run Unit calculation tests
php artisan test --filter=PremiumCalculatorTest

# Run API lifecycle feature tests
php artisan test --filter=PolicyRequestApiTest
```

Expected result:
```
PASS  Tests\Unit\PremiumCalculatorTest (6 tests)
PASS  Tests\Feature\PolicyRequestApiTest (11 tests)
Total: 19 passed (62 assertions)
```

---

## Database Migration Guide (SQLite to MySQL / PostgreSQL)

The database schema and migrations are fully portable and follow Eloquent standards. To switch to MySQL or PostgreSQL:

### For MySQL 8+:
1. Update `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=policyflow
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```
2. Run migrations:
   ```bash
   php artisan migrate:fresh
   ```

### For PostgreSQL:
1. Update `.env`:
   ```dotenv
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=policyflow
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```
2. Run migrations:
   ```bash
   php artisan migrate:fresh
   ```

---

## Fictional Demonstration Disclaimer

PolicyFlow is an assessment project created solely for demonstration purposes. It is not an authorized financial distributor, underwriter, or insurer. All insurance plans, rates, policy numbers, and quotes are strictly fictional.
