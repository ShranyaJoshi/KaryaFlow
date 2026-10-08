// public/js/escrow.js - Multi-Signature Escrow & Stamping Controller

async function signParty(party) {
    const btn = document.getElementById(`btn-sign-${party}`);
    const badge = document.getElementById(`${party}-status`);

    if (btn) btn.disabled = true;

    try {
        const res = await fetch('api/fabric_gateway.php?action=sign', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ party })
        });
        const data = await res.json();

        if (data.success) {
            if (badge) {
                badge.className = 'badge bg-success my-2';
                badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed & Stamped';
            }

            const txDisplay = document.getElementById('tx-display');
            const txHashVal = document.getElementById('tx-hash-val');
            if (txDisplay && txHashVal) {
                txDisplay.classList.remove('d-none');
                txHashVal.textContent = data.tx_hash;
            }

            const buyerSigned = document.getElementById('buyer-status')?.textContent.includes('Signed');
            const vendorSigned = document.getElementById('vendor-status')?.textContent.includes('Signed');

            const escrowBadge = document.getElementById('escrow-badge');
            if (buyerSigned && vendorSigned && escrowBadge) {
                escrowBadge.className = 'badge bg-success';
                escrowBadge.textContent = 'SETTLEMENT COMMITTED (DUAL-KEY CONFIRMED)';
            }

            if (typeof fetchAuditLogs === 'function') {
                await fetchAuditLogs();
            }
        } else {
            if (btn) btn.disabled = false;
        }
    } catch (e) {
        console.error('Escrow sign error:', e);
        if (btn) btn.disabled = false;
    }
}