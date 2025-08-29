<!-- Owner Info Modal Component -->
<div id="owner-info-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="owner-modal-title">Owner Information</h3>
            <button class="modal-close" onclick="closeModal('owner-info-modal')">×</button>
        </div>
        <div class="modal-body" id="owner-modal-content">
            <!-- Owner details will be loaded here -->
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading owner information...</p>
            </div>
        </div>
    </div>
</div>

<style>
    .owner-info-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .owner-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .owner-header h2 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .owner-header .owner-role {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        display: inline-block;
    }

    .owner-details {
        padding: 1.5rem;
    }

    .detail-section {
        margin-bottom: 2rem;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-grid {
        display: grid;
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #e2e8f0;
    }

    .detail-label {
        font-weight: 500;
        color: #64748b;
        font-size: 0.875rem;
    }

    .detail-value {
        font-weight: 600;
        color: #1e293b;
        text-align: right;
    }

    .bank-detail-item {
        border-left-color: #10b981;
    }

    .contact-detail-item {
        border-left-color: #3b82f6;
    }

    .property-count {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        margin-top: 1rem;
    }

    .property-count h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.25rem;
    }

    .property-count p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .loading-state {
        text-align: center;
        padding: 3rem;
        color: #64748b;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e2e8f0;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .no-bank-info {
        text-align: center;
        padding: 2rem;
        color: #64748b;
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .no-bank-info h4 {
        color: #92400e;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .detail-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .detail-value {
            text-align: left;
        }
    }
</style>

<script>
function viewOwnerInfo(ownerId, propertyName) {
    // Set modal title
    document.getElementById('owner-modal-title').textContent = `Owner Info - ${propertyName}`;
    
    // Show loading state
    document.getElementById('owner-modal-content').innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner"></div>
            <p>Loading owner information...</p>
        </div>
    `;
    
    // Open modal
    openModal('owner-info-modal');
    
    // Fetch owner data
    fetch(`/admin/owners/${ownerId}/info`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('owner-modal-content').innerHTML = formatOwnerInfo(data.owner, data.bankInfo, propertyName);
            } else {
                document.getElementById('owner-modal-content').innerHTML = `
                    <div class="no-bank-info">
                        <h4>❌ Error Loading Data</h4>
                        <p>${data.message || 'Unable to load owner information'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('owner-modal-content').innerHTML = `
                <div class="no-bank-info">
                    <h4>❌ Connection Error</h4>
                    <p>Unable to fetch owner information. Please try again.</p>
                </div>
            `;
        });
}

function formatOwnerInfo(owner, bankInfo, propertyName) {
    const bankSection = bankInfo ? `
        <div class="detail-section">
            <div class="section-title">
                🏦 Banking Information
            </div>
            <div class="detail-grid">
                <div class="detail-item bank-detail-item">
                    <span class="detail-label">Bank Name</span>
                    <span class="detail-value">${bankInfo.bank_name}</span>
                </div>
                <div class="detail-item bank-detail-item">
                    <span class="detail-label">Account Number</span>
                    <span class="detail-value">${bankInfo.account_number}</span>
                </div>
                ${bankInfo.iban ? `
                <div class="detail-item bank-detail-item">
                    <span class="detail-label">IBAN</span>
                    <span class="detail-value">${bankInfo.iban}</span>
                </div>
                ` : ''}
                ${bankInfo.swift ? `
                <div class="detail-item bank-detail-item">
                    <span class="detail-label">SWIFT Code</span>
                    <span class="detail-value">${bankInfo.swift}</span>
                </div>
                ` : ''}
                ${bankInfo.bank_address ? `
                <div class="detail-item bank-detail-item">
                    <span class="detail-label">Bank Address</span>
                    <span class="detail-value">${bankInfo.bank_address}</span>
                </div>
                ` : ''}
            </div>
        </div>
    ` : `
        <div class="no-bank-info">
            <h4>⚠️ No Banking Information</h4>
            <p>No banking details have been set up for this owner yet.</p>
        </div>
    `;

    return `
        <div class="owner-info-card">
            <div class="owner-header">
                <h2>${owner.full_name}</h2>
                <span class="owner-role">Property Owner</span>
            </div>
            
            <div class="owner-details">
                <div class="detail-section">
                    <div class="section-title">
                        👤 Contact Information
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item contact-detail-item">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">${owner.email}</span>
                        </div>
                        ${owner.phone ? `
                        <div class="detail-item contact-detail-item">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value">${owner.phone}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>

                ${bankSection}

                <div class="property-count">
                    <h3>${owner.houses_count} Properties</h3>
                    <p>${owner.suites_count} Total Suites</p>
                </div>
            </div>
        </div>
    `;
}
</script>