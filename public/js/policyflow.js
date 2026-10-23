/**
 * PolicyFlow - Frontend Application Logic
 * Vanilla JavaScript for Calculator, Policy Submission, and Tracking
 */

function initPolicyFlow() {
  // Mobile Nav Toggle
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  }

  // Elements
  const policyForm = document.getElementById('policyRequestForm');
  const btnCalculate = document.getElementById('btnCalculate');
  const btnSubmit = document.getElementById('btnSubmit');
  const btnGenerateId = document.getElementById('btnGenerateId');
  const clientRequestIdInput = document.getElementById('client_request_id');
  const agentIdInput = document.getElementById('agent_id');
  const customerNameInput = document.getElementById('customer_name');
  const dobInput = document.getElementById('date_of_birth');
  const destinationSelect = document.getElementById('destination');
  const tripStartInput = document.getElementById('trip_start');
  const tripEndInput = document.getElementById('trip_end');
  const planCodeSelect = document.getElementById('plan_code');
  const sumInsuredSelect = document.getElementById('sum_insured');

  // Estimate UI elements
  const estBigPrice = document.getElementById('estBigPrice');
  const estDays = document.getElementById('estDays');
  const estBaseRate = document.getElementById('estBaseRate');
  const estAgeMult = document.getElementById('estAgeMult');
  const estDestMult = document.getElementById('estDestMult');
  const estCovMult = document.getElementById('estCovMult');
  const formAlert = document.getElementById('formAlert');

  // Tracking elements
  const trackForm = document.getElementById('trackForm');
  const trackIdInput = document.getElementById('trackIdInput');
  const btnTrack = document.getElementById('btnTrack');
  const trackAlert = document.getElementById('trackAlert');
  const trackResultCard = document.getElementById('trackResultCard');
  const recentRequestsList = document.getElementById('recentRequestsList');

  // Auto-generate memorable 6-digit numeric application/client request ID
  function generateClientId() {
    return Math.floor(100000 + Math.random() * 900000).toString();
  }

  if (btnGenerateId && clientRequestIdInput) {
    btnGenerateId.addEventListener('click', () => {
      clientRequestIdInput.value = generateClientId();
      clearError(clientRequestIdInput);
    });
  }

  // Ensure application ID and agent ID only accept numbers for easy memorization
  if (clientRequestIdInput) {
    clientRequestIdInput.addEventListener('input', () => {
      clientRequestIdInput.value = clientRequestIdInput.value.replace(/\D/g, '');
    });
  }
  if (agentIdInput) {
    agentIdInput.addEventListener('input', () => {
      agentIdInput.value = agentIdInput.value.replace(/\D/g, '');
    });
  }

  // Initialize dates and IDs with sensible defaults if empty
  const today = new Date();
  const nextMonth = new Date();
  nextMonth.setDate(today.getDate() + 14);
  const nextMonthEnd = new Date();
  nextMonthEnd.setDate(nextMonth.getDate() + 7);

  if (tripStartInput && !tripStartInput.value) {
    tripStartInput.value = nextMonth.toISOString().split('T')[0];
  }
  if (tripEndInput && !tripEndInput.value) {
    tripEndInput.value = nextMonthEnd.toISOString().split('T')[0];
  }
  if (dobInput && !dobInput.value) {
    const defaultDob = new Date();
    defaultDob.setFullYear(today.getFullYear() - 30);
    dobInput.value = defaultDob.toISOString().split('T')[0];
  }
  if (clientRequestIdInput && (!clientRequestIdInput.value || clientRequestIdInput.value.startsWith('REQ-'))) {
    clientRequestIdInput.value = generateClientId();
  }
  if (agentIdInput && (!agentIdInput.value || agentIdInput.value === 'AGENT-101' || agentIdInput.value === 'AGENT-42')) {
    agentIdInput.value = '1001';
  }

  // Clear validation styling
  function clearAllErrors() {
    document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => {
      el.textContent = '';
      el.classList.remove('show');
    });
    if (formAlert) {
      formAlert.className = 'alert-box';
      formAlert.textContent = '';
    }
  }

  function setError(inputEl, message) {
    if (!inputEl) return;
    inputEl.classList.add('is-invalid');
    const feedback = inputEl.closest('.form-group')?.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.textContent = message;
      feedback.classList.add('show');
    }
  }

  function clearError(inputEl) {
    if (!inputEl) return;
    inputEl.classList.remove('is-invalid');
    const feedback = inputEl.closest('.form-group')?.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.textContent = '';
      feedback.classList.remove('show');
    }
  }

  // Debounced live calculation
  let calcDebounce = null;
  function triggerEstimate() {
    clearTimeout(calcDebounce);
    calcDebounce = setTimeout(calculateEstimate, 300);
  }

  [dobInput, tripStartInput, tripEndInput, destinationSelect, planCodeSelect, sumInsuredSelect].forEach(el => {
    if (el) {
      el.addEventListener('change', triggerEstimate);
      el.addEventListener('input', triggerEstimate);
    }
  });

  if (btnCalculate) {
    btnCalculate.addEventListener('click', calculateEstimate);
  }

  async function calculateEstimate() {
    if (!dobInput.value || !tripStartInput.value || !tripEndInput.value) {
      return;
    }

    if (new Date(tripEndInput.value) < new Date(tripStartInput.value)) {
      setError(tripEndInput, 'Trip end date cannot be earlier than start date.');
      return;
    } else {
      clearError(tripEndInput);
    }

    try {
      const payload = {
        date_of_birth: dobInput.value,
        destination: destinationSelect.value,
        trip_start: tripStartInput.value,
        trip_end: tripEndInput.value,
        plan_code: planCodeSelect.value,
        sum_insured: parseInt(sumInsuredSelect.value, 10),
      };

      const res = await fetch('/api/calculate-premium', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        return;
      }

      const data = await res.json();
      updateEstimateUI(data);
    } catch (err) {
      console.error('Estimate calculation failed:', err);
    }
  }

  function updateEstimateUI(data) {
    if (estBigPrice) {
      estBigPrice.textContent = `₹${parseFloat(data.premium).toFixed(2)}`;
    }
    if (estDays) {
      estDays.textContent = `${data.days} days`;
    }
    if (estBaseRate) {
      estBaseRate.textContent = `₹${data.base_rate}/day`;
    }
    if (estAgeMult) {
      estAgeMult.textContent = `${data.age_multiplier.toFixed(2)}x (Age: ${data.age})`;
    }
    if (estDestMult) {
      estDestMult.textContent = `${data.destination_multiplier.toFixed(2)}x`;
    }
    if (estCovMult) {
      estCovMult.textContent = `${data.coverage_multiplier.toFixed(2)}x`;
    }
  }

  // Initial estimate calculation
  calculateEstimate();

  // Plan card selection buttons
  document.querySelectorAll('.btn-select-plan').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const selectedPlan = e.currentTarget.getAttribute('data-plan');
      if (planCodeSelect && selectedPlan) {
        planCodeSelect.value = selectedPlan;
        calculateEstimate();
        document.getElementById('calculatorSection')?.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Policy Request Form Submission
  if (policyForm) {
    policyForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAllErrors();

      // Client-side validation
      let hasError = false;
      if (!clientRequestIdInput.value.trim()) {
        setError(clientRequestIdInput, 'Client Request ID is required.');
        hasError = true;
      }
      if (!agentIdInput.value.trim()) {
        setError(agentIdInput, 'Agent ID is required.');
        hasError = true;
      }
      if (!customerNameInput.value.trim()) {
        setError(customerNameInput, 'Customer name is required.');
        hasError = true;
      }
      if (!dobInput.value) {
        setError(dobInput, 'Date of birth is required.');
        hasError = true;
      }
      if (!tripStartInput.value) {
        setError(tripStartInput, 'Trip start date is required.');
        hasError = true;
      }
      if (!tripEndInput.value) {
        setError(tripEndInput, 'Trip end date is required.');
        hasError = true;
      }
      if (tripStartInput.value && tripEndInput.value && new Date(tripEndInput.value) < new Date(tripStartInput.value)) {
        setError(tripEndInput, 'Trip end date cannot be earlier than start date.');
        hasError = true;
      }
      if (dobInput.value && tripStartInput.value && new Date(dobInput.value) > new Date(tripStartInput.value)) {
        setError(dobInput, 'Date of birth cannot be after trip start date.');
        hasError = true;
      }

      if (hasError) {
        showFormAlert('Please fix the errors indicated in the form.', 'danger');
        return;
      }

      // Prevent duplicate submissions by disabling button & setting loading state
      btnSubmit.disabled = true;
      const originalSubmitText = btnSubmit.innerHTML;
      btnSubmit.innerHTML = `<span class="spinner"></span> Processing Request...`;

      const payload = {
        client_request_id: clientRequestIdInput.value.trim(),
        agent_id: agentIdInput.value.trim(),
        customer_name: customerNameInput.value.trim(),
        date_of_birth: dobInput.value,
        destination: destinationSelect.value,
        trip_start: tripStartInput.value,
        trip_end: tripEndInput.value,
        plan_code: planCodeSelect.value,
        sum_insured: parseInt(sumInsuredSelect.value, 10),
      };

      try {
        const response = await fetch('/api/policy-requests', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.status === 201) {
          // Successfully created
          showFormAlert(
            `Policy request created successfully! Request ID: #${data.id} (Ref: ${data.client_request_id}). Status: ${data.status}. Premium: ₹${data.premium}`,
            'success',
            data.id
          );
          loadRecentRequests();
        } else if (response.status === 200) {
          // Idempotent duplicate match
          showFormAlert(
            `Existing request detected for Agent ${data.agent_id} and Ref ${data.client_request_id}. Retrieved policy #${data.id} (Status: ${data.status}).`,
            'info',
            data.id
          );
          loadRecentRequests();
        } else if (response.status === 422) {
          // Laravel validation errors
          if (data.errors) {
            Object.keys(data.errors).forEach(field => {
              const input = document.getElementById(field);
              if (input) {
                setError(input, data.errors[field][0]);
              }
            });
          }
          showFormAlert(data.message || 'Validation failed. Please verify your entries.', 'danger');
        } else {
          showFormAlert(data.message || 'An unexpected error occurred while processing the request.', 'danger');
        }
      } catch (error) {
        console.error('Submission network error:', error);
        showFormAlert('Network error occurred. Please check your connection and try again.', 'danger');
      } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalSubmitText;
      }
    });
  }

  function showFormAlert(message, type, policyId = null) {
    if (!formAlert) return;
    formAlert.className = `alert-box alert-${type} show`;
    let html = `<div>${message}</div>`;
    if (policyId) {
      html += `<div style="margin-top: 8px;">
        <button type="button" class="btn btn-sm btn-primary" onclick="trackPolicy('${policyId}')">
          View in Tracker &rarr;
        </button>
      </div>`;
    }
    formAlert.innerHTML = html;
  }

  // Policy Tracking Search
  if (trackForm) {
    trackForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = trackIdInput.value.trim();
      if (!id) {
        showTrackAlert('Please enter a Policy Request ID or Client Reference.', 'danger');
        return;
      }
      trackPolicy(id);
    });
  }

  window.trackPolicy = async function (id) {
    if (!id) return;
    if (trackIdInput) trackIdInput.value = id;

    if (btnTrack) {
      btnTrack.disabled = true;
      btnTrack.innerHTML = `<span class="spinner"></span> Finding...`;
    }

    if (trackAlert) {
      trackAlert.className = 'alert-box';
      trackAlert.textContent = '';
    }

    try {
      const res = await fetch(`/api/policy-requests/${encodeURIComponent(id)}`, {
        headers: { 'Accept': 'application/json' },
      });

      if (!res.ok) {
        if (res.status === 404) {
          showTrackAlert(`No policy request found matching identifier "${id}".`, 'danger');
        } else {
          showTrackAlert('Failed to retrieve policy request.', 'danger');
        }
        if (trackResultCard) trackResultCard.classList.remove('show');
        return;
      }

      const policy = await res.json();
      renderTrackingResult(policy);
      document.getElementById('trackingSection')?.scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
      console.error('Tracking fetch error:', err);
      showTrackAlert('Network error while retrieving policy request.', 'danger');
    } finally {
      if (btnTrack) {
        btnTrack.disabled = false;
        btnTrack.innerHTML = 'Track Request';
      }
    }
  };

  function showTrackAlert(msg, type) {
    if (!trackAlert) return;
    trackAlert.className = `alert-box alert-${type} show`;
    trackAlert.textContent = msg;
  }

  function renderTrackingResult(policy) {
    if (!trackResultCard) return;

    // Badge styling class
    const statusClass = policy.status || 'RECEIVED';

    let policyNumberHtml = '';
    if (policy.status === 'ISSUED' && policy.policy_number) {
      policyNumberHtml = `
        <div class="policy-number-banner">
          <div>
            <div class="banner-label">OFFICIALLY ISSUED POLICY NUMBER</div>
            <div class="banner-number">${policy.policy_number}</div>
          </div>
          <span class="preview-status" style="background-color: #22C55E; color: white;">
            Active & Verified
          </span>
        </div>
      `;
    }

    let actionButtonsHtml = '';
    if (policy.status !== 'ISSUED') {
      actionButtonsHtml = `
        <button type="button" class="btn btn-primary btn-sm" id="btnSubmitToInsurer" onclick="submitToInsurer(${policy.id})">
          ${policy.status === 'PENDING' ? 'Re-Submit to Insurer' : 'Submit for Insurer Issuance'}
        </button>
      `;
    }

    // Interactive mock insurer controls for test demonstration
    const insurerSimulationHtml = `
      <div class="insurer-controls-box">
        <h4>
          <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
          Simulate Insurer Webhook Callback (Assessment Demo)
        </h4>
        <p>Test asynchronous insurer response callbacks for provider reference: <code>${policy.provider_reference || 'Not yet assigned'}</code></p>
        <div class="insurer-actions">
          <button type="button" class="btn btn-secondary btn-sm" ${!policy.provider_reference ? 'disabled' : ''} onclick="triggerMockCallback('${policy.provider_reference}', 'ISSUED')">
            Simulate Webhook: APPROVE & ISSUE
          </button>
          <button type="button" class="btn btn-secondary btn-sm" ${!policy.provider_reference ? 'disabled' : ''} onclick="triggerMockCallback('${policy.provider_reference}', 'FAILED')">
            Simulate Webhook: REJECT (FAILED)
          </button>
        </div>
      </div>
    `;

    trackResultCard.innerHTML = `
      <div class="tracking-result-header">
        <div>
          <span class="tracking-id-label">POLICY REQUEST ID #${policy.id}</span>
          <div class="tracking-id-val">${policy.customer_name}</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
          <span class="status-badge ${statusClass}">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor;"></span>
            ${policy.status}
          </span>
          ${actionButtonsHtml}
        </div>
      </div>

      ${policyNumberHtml}

      <div class="tracking-details-grid">
        <div class="tracking-detail-item">
          <div class="label">Client Request ID</div>
          <div class="val">${policy.client_request_id}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Agent ID</div>
          <div class="val">${policy.agent_id}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Premium Amount</div>
          <div class="val" style="color:var(--color-main-blue); font-size:1.1rem;">₹${parseFloat(policy.premium).toFixed(2)}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Destination</div>
          <div class="val">${policy.destination}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Selected Plan</div>
          <div class="val">${policy.plan_code}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Sum Insured</div>
          <div class="val">₹${parseInt(policy.sum_insured).toLocaleString('en-IN')}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Trip Start Date</div>
          <div class="val">${policy.trip_start ? policy.trip_start.split('T')[0] : 'N/A'}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Trip End Date</div>
          <div class="val">${policy.trip_end ? policy.trip_end.split('T')[0] : 'N/A'}</div>
        </div>
        <div class="tracking-detail-item">
          <div class="label">Provider Reference</div>
          <div class="val" style="font-family:monospace; font-size:0.85rem;">${policy.provider_reference || 'Pending submission'}</div>
        </div>
      </div>

      ${policy.last_response ? `
        <div style="font-size:0.8rem; color:var(--color-muted-text); background:#F1F5F9; padding:8px 12px; border-radius:4px; margin-bottom:16px;">
          <strong>Last Logged Response:</strong> ${escapeHtml(policy.last_response)}
        </div>
      ` : ''}

      ${insurerSimulationHtml}
    `;

    trackResultCard.classList.add('show');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }

  // Submit to insurer action
  window.submitToInsurer = async function (id) {
    try {
      const btn = document.getElementById('btnSubmitToInsurer');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner"></span> Submitting...`;
      }

      const res = await fetch(`/api/policy-requests/${id}/submit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });

      const data = await res.json();
      if (res.ok) {
        showTrackAlert(data.message || 'Submitted to insurer successfully.', 'success');
        renderTrackingResult(data.policy);
        loadRecentRequests();
      } else {
        showTrackAlert(data.message || 'Failed to submit policy request.', 'danger');
      }
    } catch (err) {
      console.error('Submit to insurer error:', err);
      showTrackAlert('Error connecting to insurer service.', 'danger');
    }
  };

  // Mock callback trigger
  window.triggerMockCallback = async function (providerRef, status) {
    if (!providerRef) {
      showTrackAlert('Please submit the policy first to generate a provider reference.', 'warning');
      return;
    }

    try {
      const payload = {
        provider_reference: providerRef,
        status: status,
      };

      if (status === 'ISSUED') {
        const randNum = Math.floor(100000 + Math.random() * 900000);
        payload.policy_number = `POL-${new Date().getFullYear()}-${randNum}`;
      } else {
        payload.error_message = 'Underwriting risk score exceeded threshold.';
      }

      const res = await fetch('/api/mock-insurer/callback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      if (res.ok) {
        showTrackAlert(data.message, status === 'ISSUED' ? 'success' : 'warning');
        renderTrackingResult(data.policy);
        loadRecentRequests();
      } else {
        showTrackAlert(data.message || 'Callback failed.', 'danger');
      }
    } catch (err) {
      console.error('Callback trigger error:', err);
      showTrackAlert('Error sending mock webhook callback.', 'danger');
    }
  };

  // Load Recent Requests in Tracker
  async function loadRecentRequests() {
    if (!recentRequestsList) return;

    try {
      const res = await fetch('/api/policy-requests?page=1', {
        headers: { 'Accept': 'application/json' },
      });
      if (!res.ok) return;

      const data = await res.json();
      const items = data.data || [];

      if (items.length === 0) {
        recentRequestsList.innerHTML = `<tr><td colspan="6" style="text-align:center; color:var(--color-muted-text); padding:16px;">No policy requests submitted yet. Use the form above to submit your first request.</td></tr>`;
        return;
      }

      recentRequestsList.innerHTML = items.slice(0, 5).map(item => `
        <tr onclick="trackPolicy('${item.id}')">
          <td style="font-weight:600;">#${item.id}</td>
          <td>${escapeHtml(item.client_request_id)}</td>
          <td>${escapeHtml(item.customer_name)}</td>
          <td>₹${parseFloat(item.premium).toFixed(2)}</td>
          <td><span class="status-badge ${item.status}" style="font-size:0.75rem; padding:2px 8px;">${item.status}</span></td>
          <td style="font-family:monospace; font-size:0.8rem;">${item.policy_number || '—'}</td>
        </tr>
      `).join('');
    } catch (err) {
      console.error('Failed to load recent requests:', err);
    }
  }

  loadRecentRequests();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPolicyFlow);
} else {
  initPolicyFlow();
}
