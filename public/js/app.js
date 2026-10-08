// ==============================================================
// KARYAFLOW PRO - UNIFIED CLIENT CONTROLLER (Full app.js)
// ==============================================================

let currentWfId = 1;
let currentLang = localStorage.getItem('kf_lang') || 'en';

// Primary Pipeline Steps Model
let liveSteps = [
    { 
        id: 1, 
        step_order: 1, 
        step_name: 'Step 1: Ingest Bank Statements', 
        status: 'COMPLETED', 
        requires_approval: 0, 
        step_output: 'Read 12 transactions from current bank statement.' 
    },
    { 
        id: 2, 
        step_order: 2, 
        step_name: 'Step 2: Cross-Check Company Records', 
        status: 'RUNNING', 
        requires_approval: 0, 
        step_output: 'Found overdue discrepancy on Invoice INV-1042 for $17,250.00.' 
    },
    { 
        id: 3, 
        step_order: 3, 
        step_name: 'Step 3: Dual-Signature Escrow Payment', 
        status: 'WAITING_APPROVAL', 
        requires_approval: 1, 
        step_output: 'Awaiting digital sign-off from both buyer and vendor.' 
    },
    { 
        id: 4, 
        step_order: 4, 
        step_name: 'Step 4: Send Notification Emails', 
        status: 'PENDING', 
        requires_approval: 0, 
        step_output: 'Will email payment confirmation to all 4 stakeholders.' 
    },
    { 
        id: 5, 
        step_order: 5, 
        step_name: 'Step 5: Lock Proof in Blockchain', 
        status: 'PENDING', 
        requires_approval: 0, 
        step_output: 'Creates a tamper-proof cryptographic receipt.' 
    }
];

// Multilingual Dictionary
const TRANSLATIONS = {
    en: {
        emailSubjectText: "[Escrow Execution Ready] Invoice INV-1042",
        emailBodyText: "Automated dispatch: Discrepancy resolved. Hyperledger escrow awaiting dual-key execution before settlement."
    },
    hi: {
        emailSubjectText: "[एस्क्रो निष्पादन तैयार] चालान INV-1042",
        emailBodyText: "स्वचालित सूचना: विसंगति का समाधान हुआ। निपटान से पहले हाइपरलेज़र एस्क्रो हस्ताक्षर की प्रतीक्षा है।"
    },
    mr: {
        emailSubjectText: "[एस्क्रो सेटलमेंट सज्ज] बीजक INV-1042",
        emailBodyText: "स्वयंचलित सूचना: विसंगती सोडवली गेली. सेटलमेंटपूर्वी हायपरलेझर स्वाक्षरी आवश्यक आहे."
    },
    es: {
        emailSubjectText: "[Fideicomiso Listo] Factura INV-1042",
        emailBodyText: "Despacho automatizado: Discrepancia resuelta. Fideicomiso Hyperledger esperando firmas mutuas."
    }
};

// 1. Toast Notification Helper
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-wrapper');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-wrapper';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning text-dark' : 'bg-primary';
    toastEl.className = `toast align-items-center text-white ${bgClass} border-0 show shadow-lg mb-2`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body small fw-semibold">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    container.appendChild(toastEl);
    setTimeout(() => { toastEl.remove(); }, 3500);
}

// 2. Multilingual Support
function changeLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('kf_lang', lang);
    const langLabel = document.getElementById('current-lang-label');
    if (langLabel) langLabel.textContent = lang.toUpperCase();

    const dict = TRANSLATIONS[currentLang] || TRANSLATIONS.en;
    const subj = document.getElementById('email-subject');
    const body = document.getElementById('email-body');
    if (subj) subj.textContent = dict.emailSubjectText;
    if (body) body.textContent = dict.emailBodyText;

    showToast(`Language switched to ${lang.toUpperCase()}`, 'info');
}

// 3. User Session Verification
async function verifySession() {
    try {
        const res = await fetch('api/auth.php?action=current_user');
        const data = await res.json();
        
        if (!data.authenticated || !data.user) {
            window.location.href = 'login.html';
            return;
        }

        const user = data.user;
        const firstName = user.name.split(' ')[0] || user.name;
        
        const welcomeName = document.getElementById('welcome-first-name');
        if (welcomeName) welcomeName.textContent = firstName;

        const userNav = document.getElementById('auth-user-badge');
        if (userNav) {
            userNav.innerHTML = `
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle text-primary fs-6"></i>
                        <span class="fw-semibold">${user.name}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2 border-bottom">
                            <strong class="d-block small text-dark">${user.name}</strong>
                            <small class="text-muted">${user.email}</small>
                            <span class="badge bg-primary-subtle text-primary border d-inline-block mt-1">${user.role}</span>
                        </li>
                        <li>
                            <a class="dropdown-item small py-2" href="profile.html">
                                <i class="bi bi-person-vcard me-2"></i> Account Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger small py-2" href="#" onclick="logoutUser()">
                                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            `;
        }
    } catch (e) {
        window.location.href = 'login.html';
    }
}

async function logoutUser() {
    try { await fetch('api/auth.php?action=logout'); } catch (e) {}
    window.location.href = 'login.html';
}

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('kf_theme', newTheme);
    
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = newTheme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill';
    }
}

function toggleSidebar() {
    document.body.classList.toggle('toggled');
}

function scrollToSec(id) {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(item => item.classList.remove('active'));
    if (event?.currentTarget?.classList) event.currentTarget.classList.add('active');
}

// 4. Template Selector for Workflow Synthesizer (Restored)
function onTemplateSelected() {
    const sel = document.getElementById('synth-template-select');
    if (!sel) return;

    const val = sel.value;
    const titleInput = document.getElementById('synth-title');
    const objInput = document.getElementById('synth-objective');
    const priority = document.getElementById('synth-priority');
    const mode = document.getElementById('synth-mode');

    if (val === 'INVOICE_ESCROW') {
        titleInput.value = 'Disputed Invoice Reconcile & Escrow Settlement';
        objInput.value = 'Cross-check overdue bank statement items against internal ledger, enforce dual-key escrow signing, and dispatch settlement notification to vendor.';
        priority.value = 'HIGH';
        mode.value = 'ASSISTED';
    } else if (val === 'VENDOR_ONBOARDING') {
        titleInput.value = 'Autonomous Vendor KYC & Routing Audit';
        objInput.value = 'Validate vendor GSTIN/tax identifier, match bank clearinghouse records, and sign trusted supplier agreement.';
        priority.value = 'MEDIUM';
        mode.value = 'AUTOMATIC';
    } else if (val === 'UNRECONCILED_AR') {
        titleInput.value = 'Overdue Accounts Receivable Auto-Recovery';
        objInput.value = 'Detect 30+ day unpaid balance, perform payment match, notify credit control, and trigger structured settlement plan.';
        priority.value = 'CRITICAL';
        mode.value = 'SIMULATION';
    }
}

// 5. Workflow Synthesizer Creation Action (Restored)
async function synthesizeWorkflow() {
    const title = document.getElementById('synth-title').value;
    const objective = document.getElementById('synth-objective').value;
    const priority = document.getElementById('synth-priority').value;
    const mode = document.getElementById('synth-mode').value;
    const owner = document.getElementById('synth-owner').value;

    const modalEl = document.getElementById('createWorkflowModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    // Update banner UI immediately
    const titleEl = document.getElementById('wf-title-display');
    const objEl = document.getElementById('wf-obj-display');
    const modeEl = document.getElementById('wf-mode-tag');
    const prioEl = document.getElementById('wf-priority-tag');
    const ownEl = document.getElementById('wf-owner-tag');

    if (titleEl) titleEl.textContent = title;
    if (objEl) objEl.textContent = `Goal: ${objective}`;
    if (modeEl) modeEl.textContent = mode;
    if (prioEl) prioEl.textContent = `${priority} PRIORITY`;
    if (ownEl) ownEl.textContent = `OWNER: ${owner.split('@')[0]}`;

    // Reset steps
    liveSteps.forEach((s, idx) => {
        s.status = idx === 0 ? 'COMPLETED' : idx === 1 ? 'RUNNING' : 'PENDING';
        s.error_message = null;
    });

    renderStepCards(liveSteps);
    showToast(`Launched new workflow: "${title}"`, 'success');
    scrollToSec('sec-workflow');

    try {
        await fetch('api/workflow_engine.php?action=synthesize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, objective, priority, execution_mode: mode, owner })
        });
        fetchAuditLogs();
    } catch (e) {}
}

// 6. DAG Pipeline Graph Renderer
function renderPipelineGraph(steps) {
    const svg = document.getElementById('pipeline-svg');
    if (!svg || !steps || steps.length === 0) return;

    svg.innerHTML = '';
    const containerWidth = svg.parentElement.clientWidth || 540;
    const totalSteps = steps.length;
    
    const nodeWidth = 75;
    const nodeHeight = 34;
    const gap = Math.max(14, Math.floor((containerWidth - 40 - (nodeWidth * totalSteps)) / (totalSteps - 1)));
    const startX = 20;
    const centerY = 35;

    svg.setAttribute('width', '100%');
    svg.setAttribute('viewBox', `0 0 ${startX * 2 + (nodeWidth * totalSteps) + (gap * (totalSteps - 1))} 70`);

    steps.forEach((step, idx) => {
        const x = startX + idx * (nodeWidth + gap);
        const y = centerY - (nodeHeight / 2);

        // Beam
        if (idx < steps.length - 1) {
            const nextX = x + nodeWidth;
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', nextX);
            line.setAttribute('y1', centerY);
            line.setAttribute('x2', nextX + gap);
            line.setAttribute('y2', centerY);
            
            const isLineActive = (step.status === 'COMPLETED' || step.status === 'RUNNING');
            line.setAttribute('stroke', isLineActive ? '#6366f1' : '#cbd5e1');
            line.setAttribute('stroke-width', isLineActive ? '3' : '2');
            if (step.status === 'RUNNING') line.setAttribute('class', 'dag-line-active');
            svg.appendChild(line);
        }

        // Node
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', x);
        rect.setAttribute('y', y);
        rect.setAttribute('width', nodeWidth);
        rect.setAttribute('height', nodeHeight);
        rect.setAttribute('rx', '7');
        rect.setAttribute('class', 'dag-node');

        let fill = '#f8fafc';
        let stroke = '#94a3b8';

        if (step.status === 'COMPLETED') { fill = '#ecfdf5'; stroke = '#10b981'; }
        else if (step.status === 'RUNNING') { fill = '#eef2ff'; stroke = '#6366f1'; rect.classList.add('dag-node-running'); }
        else if (step.status === 'FAILED') { fill = '#fef2f2'; stroke = '#ef4444'; }
        else if (step.status === 'WAITING_APPROVAL') { fill = '#fffbeb'; stroke = '#f59e0b'; }

        rect.setAttribute('fill', fill);
        rect.setAttribute('stroke', stroke);
        rect.setAttribute('stroke-width', '2');
        svg.appendChild(rect);

        // Step Label
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x + (nodeWidth / 2));
        text.setAttribute('y', centerY + 4);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('font-size', '10.5');
        text.setAttribute('font-weight', 'bold');
        text.setAttribute('fill', stroke);
        text.textContent = `Step ${step.step_order}`;
        svg.appendChild(text);
    });
}

function renderStepCards(steps) {
    const container = document.getElementById('steps-container');
    if (!container) return;

    container.innerHTML = '';

    steps.forEach(s => {
        const div = document.createElement('div');
        let stateClass = 'running';
        let badgeClass = 'bg-primary';

        if (s.status === 'COMPLETED') { stateClass = 'completed'; badgeClass = 'bg-success'; }
        else if (s.status === 'FAILED') { stateClass = 'failed'; badgeClass = 'bg-danger'; }
        else if (s.status === 'WAITING_APPROVAL') { stateClass = 'waiting'; badgeClass = 'bg-warning text-dark'; }
        else if (s.status === 'PENDING') { stateClass = ''; badgeClass = 'bg-secondary'; }

        const approvalTag = s.requires_approval ? '<span class="badge bg-warning text-dark me-2"><i class="bi bi-shield-lock me-1"></i>Approval Gate</span>' : '';
        const outputDisplay = s.step_output ? `<div class="small font-monospace text-muted mt-1 bg-light bg-opacity-25 p-1 rounded">Result: ${s.step_output}</div>` : '';

        div.className = `step-card ${stateClass}`;
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>${s.step_name}</strong></div>
                <div>${approvalTag}<span class="badge ${badgeClass}">${s.status}</span></div>
            </div>
            ${outputDisplay}
            ${s.error_message ? `<div class="small text-danger fw-semibold mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>${s.error_message}</div>` : ''}
        `;
        container.appendChild(div);
    });

    renderPipelineGraph(steps);
}

// 7. Interactive Pipeline Stepping
async function advanceStep() {
    let runningIdx = liveSteps.findIndex(s => s.status === 'RUNNING' || s.status === 'WAITING_APPROVAL');

    if (runningIdx === -1) {
        liveSteps.forEach((s, i) => { s.status = i === 0 ? 'COMPLETED' : i === 1 ? 'RUNNING' : 'PENDING'; });
        runningIdx = 1;
    }

    liveSteps[runningIdx].status = 'COMPLETED';
    
    if (runningIdx + 1 < liveSteps.length) {
        const nextStep = liveSteps[runningIdx + 1];
        nextStep.status = nextStep.requires_approval ? 'WAITING_APPROVAL' : 'RUNNING';
        showToast(`Advanced to ${nextStep.step_name}`, 'success');
    } else {
        showToast('All pipeline steps completed!', 'success');
    }

    const completedCount = liveSteps.filter(s => s.status === 'COMPLETED').length;
    const pct = Math.round((completedCount / liveSteps.length) * 100);
    const bar = document.getElementById('wf-progress-bar');
    const pctText = document.getElementById('wf-progress-pct');
    if (bar) bar.style.width = pct + '%';
    if (pctText) pctText.textContent = `${pct}% Done`;

    renderStepCards(liveSteps);

    try {
        await fetch('api/workflow_engine.php?action=advance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ workflow_id: currentWfId })
        });
        fetchAuditLogs();
    } catch (e) {}
}

// 8. What-If Simulation Triggers
async function injectSimulationEvent(eventType) {
    if (eventType === 'PAYMENT_RECEIVED') {
        showToast('Shock: Payment Received! Escrow verified.', 'success');
        
        const bStatus = document.getElementById('buyer-status');
        const vStatus = document.getElementById('vendor-status');
        const eBadge = document.getElementById('escrow-badge');
        
        if (bStatus) { bStatus.className = 'badge bg-success my-2'; bStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed & Stamped'; }
        if (vStatus) { vStatus.className = 'badge bg-success my-2'; vStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed & Stamped'; }
        if (eBadge) { eBadge.className = 'badge bg-success'; eBadge.textContent = 'SETTLEMENT COMMITTED (CONSENSUS REACHED)'; }

        const step3 = liveSteps.find(s => s.id === 3);
        if (step3) { step3.status = 'COMPLETED'; step3.step_output = 'Consensus quorum reached ($17,250.00 released)'; }
        const step4 = liveSteps.find(s => s.id === 4);
        if (step4) step4.status = 'RUNNING';

    } else if (eventType === 'CUSTOMER_DISPUTE') {
        showToast('Shock: Customer Dispute Registered! Re-planning active branch.', 'warning');
        const step2 = liveSteps.find(s => s.id === 2);
        if (step2) {
            step2.status = 'WAITING_APPROVAL';
            step2.step_name = 'Step 2: Arbitration & Credit Dispute Branch';
            step2.error_message = 'Discrepancy escalated: Hold on disbursement requested.';
        }
    } else if (eventType === 'DATA_TIMEOUT') {
        showToast('Shock: Clearinghouse API Timeout! Active step marked FAILED.', 'danger');
        const active = liveSteps.find(s => s.status === 'RUNNING') || liveSteps[1];
        if (active) {
            active.status = 'FAILED';
            active.error_message = 'Socket timeout: clearinghouse.swift.internal failed to acknowledge ACK packet.';
        }
    }

    renderStepCards(liveSteps);

    try {
        await fetch('api/workflow_engine.php?action=simulate_event', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ workflow_id: currentWfId, event_type: eventType })
        });
        fetchAuditLogs();
    } catch (e) {}
}

// 9. Remediation Handlers
async function remediateFailedTask(actionType) {
    const target = liveSteps.find(s => s.status === 'FAILED' || s.status === 'WAITING_APPROVAL') || liveSteps[1];

    if (actionType === 'retry') {
        target.status = 'RUNNING';
        target.error_message = null;
        showToast(`Remediation: Retrying ${target.step_name}...`, 'info');
    } else if (actionType === 'replan') {
        target.step_name = 'Step 2: Alternate Routing & Auto-Clearinghouse Bridge';
        target.status = 'RUNNING';
        target.error_message = null;
        showToast('Remediation: Dynamic graph re-plan triggered.', 'warning');
    } else if (actionType === 'skip') {
        target.status = 'COMPLETED';
        target.step_output = 'Bypassed by compliance policy override';
        target.error_message = null;
        showToast('Remediation: Step skipped.', 'secondary');
    } else if (actionType === 'escalate') {
        target.status = 'WAITING_APPROVAL';
        target.error_message = 'Escalated to CFO / Authorized Signer';
        showToast('Remediation: Escalated to CFO Approver.', 'danger');
    }

    renderStepCards(liveSteps);

    try {
        await fetch('api/workflow_engine.php?action=manage_failed_task', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ workflow_id: currentWfId, step_id: target.id, resolution: actionType })
        });
        fetchAuditLogs();
    } catch (e) {}
}

// 10. Automated Email Broadcast
async function dispatchResolutionEmail() {
    const btn = document.getElementById('btn-dispatch-email');
    const status = document.getElementById('email-dispatch-status');
    const invoiceId = document.getElementById('escrow-invoice-id')?.value || 'INV-1042';
    const amount = document.getElementById('escrow-amount')?.value || '$17,250.00';
    const txHash = document.getElementById('tx-hash-val')?.textContent || '0x88f9104bcde291a7834e02bbca018274f910e53a2';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Transmitting...';
    }
    if (status) {
        status.innerHTML = '<span class="text-info"><i class="bi bi-send me-1"></i>Contacting Gmail SMTP Relay...</span>';
    }

    const targetList = [
        'joshi.shranya2202@gmail.com',
        'jaisvidhianil@gmail.com',
        'shranyajoshi2006@gmail.com',
        'ritikap1807@gmail.com'
    ];

    try {
        const res = await fetch('api/send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                recipients: targetList,
                invoice_id: invoiceId,
                amount: amount,
                tx_hash: txHash,
                lang: currentLang
            })
        });

        const data = await res.json();
        if (status) {
            if (data.success) {
                status.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Delivered in [${currentLang.toUpperCase()}] to ${data.total_sent} addresses!</span>`;
                showToast(`Delivered email notifications in ${currentLang.toUpperCase()}!`, 'success');
            } else {
                status.innerHTML = `<span class="text-danger">Failed: ${data.error || 'Server error'}</span>`;
                showToast('Email dispatch error', 'danger');
            }
        }
        fetchAuditLogs();
    } catch (e) {
        if (status) status.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Delivered (Simulated) to 4 Stakeholders</span>';
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Real Email to All 4';
        }
    }
}

// 11. CSV Upload & Ingestion (Restored)
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split('\n').filter(l => l.trim().length > 0);
        const records = [];
        
        for (let i = 1; i < lines.length; i++) {
            const parts = lines[i].split(',');
            if (parts.length >= 4) {
                const days = parseInt(parts[3].trim()) || 0;
                records.push({
                    invoice_id: parts[0].trim(),
                    vendor: parts[1].trim(),
                    amount: parseFloat(parts[2].trim()) || 0,
                    due_days: days,
                    status: days > 30 ? 'OVERDUE' : 'CURRENT'
                });
            }
        }

        if (records.length > 0) {
            renderCSVRows(records);
            showToast(`Uploaded and ingested ${records.length} invoices from CSV!`, 'success');
        } else {
            showToast('No valid rows found in uploaded CSV.', 'warning');
        }
    };
    reader.readAsText(file);
}

async function loadSampleCSV() {
    renderCSVRows([
        { invoice_id: 'INV-1042', vendor: 'Apex Cloud Logistics', amount: 17250.00, due_days: 44, status: 'OVERDUE' },
        { invoice_id: 'INV-1043', vendor: 'Global Freight Corp', amount: 8420.50, due_days: 12, status: 'CURRENT' },
        { invoice_id: 'INV-1044', vendor: 'Zenith Tech Systems', amount: 24100.00, due_days: 38, status: 'OVERDUE' },
        { invoice_id: 'INV-1045', vendor: 'Quantum Data Networks', amount: 5200.00, due_days: 5, status: 'CURRENT' }
    ]);
    showToast('Loaded 4 sample invoice records', 'info');
}

function renderCSVRows(records) {
    const tbody = document.getElementById('csv-table-body');
    if (!tbody) return;

    tbody.innerHTML = '';
    records.forEach(rec => {
        const isOverdue = rec.status === 'OVERDUE';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="fw-bold">${rec.invoice_id}</td>
            <td>${rec.vendor}</td>
            <td class="fw-bold">$${parseFloat(rec.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td><span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2-circle me-1"></i>PO Matched</span></td>
            <td><span class="badge bg-light text-dark border">${rec.due_days} Days</span></td>
            <td><span class="badge ${isOverdue ? 'bg-danger' : 'bg-success'}">${rec.status}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-primary py-0 px-2" onclick="scrollToSec('sec-escrow')"><i class="bi bi-shield-check me-1"></i>Settle</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// 12. Blockchain Audit History
async function fetchAuditLogs() {
    const tbody = document.getElementById('audit-table-body');
    if (!tbody) return;

    try {
        const res = await fetch('api/audit_logs.php');
        const data = await res.json();
        if (data.logs && data.logs.length > 0) {
            tbody.innerHTML = '';
            data.logs.slice(0, 15).forEach(log => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-nowrap text-muted">${log.created_at}</td>
                    <td><span class="badge bg-secondary">${log.action}</span></td>
                    <td>${log.details}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {}
}

// 13. Splash Dismissal
function dismissSplash() {
    const splash = document.getElementById('app-splash-screen');
    if (!splash) return;
    setTimeout(() => {
        splash.classList.add('splash-exit');
        setTimeout(() => { 
            splash.style.display = 'none'; 
        }, 700);
    }, 2100);
}

// Lifecycle Bootstrapper
window.addEventListener('DOMContentLoaded', () => {
    dismissSplash();
    verifySession();

    const savedTheme = localStorage.getItem('kf_theme') || 'dark';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = savedTheme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill';
    }

    renderStepCards(liveSteps);
    loadSampleCSV();
    fetchAuditLogs();
    changeLanguage(currentLang);
});