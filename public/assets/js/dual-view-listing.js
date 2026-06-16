/**
 * Dual View Listing System
 * Automatically converts HTML tables into dynamic card grid layouts.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Only target index pages containing listing tables
    const table = document.querySelector('.table-responsive table.table');
    if (!table) return;

    // Only target listing tables that have headers (ths)
    const ths = table.querySelectorAll('thead th');
    if (ths.length === 0) return;

    const tableContainer = table.closest('.table-responsive');
    if (!tableContainer) return;

    // 1. Gather headers
    const headers = [];
    ths.forEach(th => {
        headers.push(th.textContent.trim());
    });

    // 2. Identify the Name/Title column index
    let nameIndex = -1;
    headers.forEach((h, i) => {
        const lower = h.toLowerCase();
        if (lower.includes('name') || lower.includes('title')) {
            nameIndex = i;
        }
    });
    // Fallback if no name/title header matches
    if (nameIndex === -1) {
        if (headers.length > 1 && (headers[0].toLowerCase().includes('id') || headers[0].toLowerCase().includes('logo') || headers[0].toLowerCase().includes('avatar') || headers[0].toLowerCase().includes('photo'))) {
            nameIndex = 1;
        } else {
            nameIndex = 0;
        }
    }

    // 3. Build Card Grid Container
    const gridContainer = document.createElement('div');
    gridContainer.className = 'dual-view-grid-container d-none p-4';

    const rowWrapper = document.createElement('div');
    rowWrapper.className = 'row g-4';
    gridContainer.appendChild(rowWrapper);

    // Check for empty placeholder row (colspan)
    const emptyPlaceholderCell = table.querySelector('tbody tr td[colspan]');
    if (emptyPlaceholderCell) {
        const col = document.createElement('div');
        col.className = 'col-12 text-center py-5 text-muted card shadow-none border';
        col.innerHTML = emptyPlaceholderCell.innerHTML;
        rowWrapper.appendChild(col);
    } else {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return;

            // Extract Title, Subtitle and Avatar
            const titleCell = cells[nameIndex];
            let title = 'N/A';
            let subtitle = '';
            let avatarHtml = '';

            if (titleCell) {
                // Check for avatar/img inside title cell
                const innerImg = titleCell.querySelector('img');
                const innerAvatar = titleCell.querySelector('.avatar-text');
                if (innerImg) {
                    avatarHtml = innerImg.outerHTML;
                } else if (innerAvatar) {
                    avatarHtml = innerAvatar.outerHTML;
                }

                // Check for primary name text
                const fwSemibold = titleCell.querySelector('.fw-semibold');
                if (fwSemibold) {
                    title = fwSemibold.textContent.trim();
                } else {
                    // Clone to strip image elements for clean title
                    const temp = titleCell.cloneNode(true);
                    const toRemove = temp.querySelectorAll('img, .avatar-text');
                    toRemove.forEach(el => el.remove());
                    title = temp.textContent.trim();
                }

                // Check for subtitle details
                const textMuted = titleCell.querySelector('.text-muted, .fs-11');
                if (textMuted) {
                    subtitle = textMuted.textContent.trim();
                }
            }

            // Fallback for avatar / logo from Column 0 if not found in title cell
            if (!avatarHtml && nameIndex > 0) {
                const firstCell = cells[0];
                if (firstCell) {
                    const firstImg = firstCell.querySelector('img');
                    const firstAvatar = firstCell.querySelector('.avatar-text');
                    if (firstImg) {
                        avatarHtml = firstImg.outerHTML;
                    } else if (firstAvatar) {
                        avatarHtml = firstAvatar.outerHTML;
                    } else {
                        const cellText = firstCell.textContent.trim();
                        if (cellText.startsWith('#') || headers[0].toLowerCase().includes('id')) {
                            subtitle = subtitle ? `${cellText} - ${subtitle}` : cellText;
                        }
                    }
                }
            }

            // Fallback: Generate Letter Avatar
            if (!avatarHtml) {
                const letter = title ? title.charAt(0).toUpperCase() : '?';
                avatarHtml = `
                    <div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-weight: 700; font-size: 16px;">
                        ${letter}
                    </div>
                `;
            }

            // Extract actions (last column)
            const actionsIndex = cells.length - 1;
            const actionsCell = cells[actionsIndex];
            let actionsHtml = '';
            if (actionsCell) {
                actionsHtml = actionsCell.innerHTML.trim();
            }

            // Compile details key-value fields
            let detailsHtml = '';
            cells.forEach((cell, idx) => {
                if (idx === nameIndex || idx === actionsIndex) return;
                // Skip column 0 if it was purely a logo/avatar
                if (idx === 0 && (headers[0].toLowerCase().includes('logo') || headers[0].toLowerCase().includes('avatar') || headers[0].toLowerCase().includes('photo'))) {
                    return;
                }

                const label = headers[idx];
                const val = cell.innerHTML.trim();
                if (label && val && val !== 'N/A') {
                    detailsHtml += `
                        <div class="dual-view-card-detail-item">
                            <span class="dual-view-card-detail-label">${label}</span>
                            <span class="dual-view-card-detail-value">${val}</span>
                        </div>
                    `;
                }
            });

            // Build Card HTML
            const col = document.createElement('div');
            col.className = 'col-12 col-md-6 col-lg-4 col-xl-4';
            col.innerHTML = `
                <div class="dual-view-card card-hover-effect">
                    <div class="card-body">
                        <div class="dual-view-card-header">
                            <div class="dual-view-card-avatar">
                                ${avatarHtml}
                            </div>
                            <div class="dual-view-card-title-area">
                                <div class="dual-view-card-title" title="${title}">${title}</div>
                                ${subtitle ? `<div class="dual-view-card-subtitle" title="${subtitle}">${subtitle}</div>` : ''}
                            </div>
                        </div>
                        <div class="dual-view-card-details">
                            ${detailsHtml}
                        </div>
                    </div>
                    ${actionsHtml ? `
                    <div class="card-footer">
                        ${actionsHtml}
                    </div>
                    ` : ''}
                </div>
            `;
            rowWrapper.appendChild(col);
        });
    }

    // Insert grid after table in DOM
    tableContainer.parentNode.insertBefore(gridContainer, tableContainer.nextSibling);

    // 4. Create and Insert Switcher
    let targetHeader = document.querySelector('.page-header-right');
    if (targetHeader) {
        targetHeader.classList.add('d-flex', 'align-items-center', 'gap-2');
    } else {
        targetHeader = document.querySelector('.card-header');
    }

    if (targetHeader) {
        const switcher = document.createElement('div');
        switcher.className = 'view-switcher-container ms-3';
        switcher.innerHTML = `
            <div class="btn-group" role="group" aria-label="View Switcher">
                <button type="button" class="btn-view" id="btn-view-table" title="Table View">
                    <i class="feather-list me-1"></i> Table
                </button>
                <button type="button" class="btn-view" id="btn-view-grid" title="Grid View">
                    <i class="feather-grid me-1"></i> Grid
                </button>
            </div>
        `;
        // Insert switcher at start of the header
        targetHeader.insertBefore(switcher, targetHeader.firstChild);

        // Bind Toggle Event Listeners
        const btnTable = document.getElementById('btn-view-table');
        const btnGrid = document.getElementById('btn-view-grid');

        if (btnTable && btnGrid) {
            btnTable.addEventListener('click', () => setView('table'));
            btnGrid.addEventListener('click', () => setView('grid'));
        }
    }

    // Toggle logic
    function setView(viewType) {
        const btnTable = document.getElementById('btn-view-table');
        const btnGrid = document.getElementById('btn-view-grid');

        // Only enforce toggling classes/display if screen is wider than 991px
        if (window.innerWidth > 991) {
            if (viewType === 'grid') {
                tableContainer.classList.add('d-none');
                gridContainer.classList.remove('d-none');
                if (btnTable) btnTable.classList.remove('active');
                if (btnGrid) btnGrid.classList.add('active');
                localStorage.setItem('admin_listing_view_pref', 'grid');
            } else {
                tableContainer.classList.remove('d-none');
                gridContainer.classList.add('d-none');
                if (btnGrid) btnGrid.classList.remove('active');
                if (btnTable) btnTable.classList.add('active');
                localStorage.setItem('admin_listing_view_pref', 'table');
            }
        } else {
            // Under 991px, force grid layout
            tableContainer.classList.add('d-none');
            gridContainer.classList.remove('d-none');
        }
    }

    // Set Default/Saved Preference
    const savedPref = localStorage.getItem('admin_listing_view_pref') || 'table';
    setView(savedPref);

    // Monitor screen resizing to handle mobile viewport transitions properly
    window.addEventListener('resize', function() {
        const currentPref = localStorage.getItem('admin_listing_view_pref') || 'table';
        setView(currentPref);
    });
});
