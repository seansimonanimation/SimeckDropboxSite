(function(){
    const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const SLOTS = 48; // 24 hours × 2

    // Time labels every 2 hours starting at midnight
    const TIME_LABELS = ['12a','2a','4a','6a','8a','10a','12p','14/2p','16/4p','18/6p','20/8p','22/10p'];
    const LABEL_INTERVAL = 4; // every 4 half-hour slots = 2 hours

    let gridData = []; // array of 7 ints (bitmasks)
    let originalData = []; // original DB state for reset

    // ── Parse the availability string into gridData ──
    function parseAvailability(str){
        return str.split('|').map(Number);
    }

    // ── Serialize gridData back to pipe string ──
    function serializeAvailability(data){
        return data.join('|');
    }

    // ── Build the grid UI ──
    function buildGrid(containerId, initialData){
        const container = document.getElementById(containerId);
        if(!container) return;
        container.innerHTML = '';
        container.style.overflowX = 'auto';
        container.style.overflowY = 'visible';
        originalData = initialData.slice(); // preserve original for reset

        gridData = initialData.slice(); // copy

        // Outer wrapper to keep everything aligned
        const wrapper = document.createElement('div');
        wrapper.style.display = 'inline-block';

        // ── Time header row ──
        const headerRow = document.createElement('div');
        headerRow.style.display = 'flex';
        headerRow.style.alignItems = 'center';
        headerRow.style.marginBottom = '4px';

        // Corner spacer (where day labels sit)
        const corner = document.createElement('div');
        corner.style.width = '80px';
        corner.style.flexShrink = '0';
        headerRow.appendChild(corner);

        // Time labels spanning the 48 cells
        for(let t = 0; t < TIME_LABELS.length; t++){
            const lbl = document.createElement('div');
            lbl.textContent = TIME_LABELS[t];
            lbl.style.width = (SLOTS / TIME_LABELS.length) * 16 + 'px'; // 4 cells per label × 16px
            lbl.style.flexShrink = '0';
            lbl.style.fontSize = '10px';
            lbl.style.color = 'var(--color-text-muted, #888)';
            lbl.style.textAlign = 'left';
            lbl.style.paddingLeft = '2px';

            lbl.style.lineHeight = '20px';
            headerRow.appendChild(lbl);
        }
        wrapper.appendChild(headerRow);

        // ── Day rows ──
        for(let d = 0; d < 7; d++){
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.marginBottom = '2px';

            // Day label
            const dayLabel = document.createElement('div');
            dayLabel.textContent = DAYS[d];
            dayLabel.style.width = '80px';
            dayLabel.style.flexShrink = '0';
            dayLabel.style.fontSize = '11px';
            dayLabel.style.fontWeight = '600';
            dayLabel.style.color = 'var(--color-text, #333)';
            dayLabel.style.paddingRight = '8px';
            dayLabel.style.boxSizing = 'border-box';
            row.appendChild(dayLabel);


            // 48 cells
            for(let s = 0; s < SLOTS; s++){
                const cell = document.createElement('div');
                cell.className = 'av-cell';
                cell.dataset.day = d;
                cell.dataset.slot = s;

                // Size
                cell.style.width = '14px';
                cell.style.height = '22px';
                cell.style.margin = '0 1px';
                cell.style.borderRadius = '3px';
                cell.style.cursor = 'pointer';
                cell.style.boxSizing = 'border-box';
                cell.style.transition = 'background 0.15s, border-color 0.15s';

                // Default state (off)
                cell.style.border = '1px solid var(--color-border, #ccc)';
                cell.style.background = 'var(--color-bg-raised, transparent)';
                // Thicker left border on top of the hour
                if(s % 2 === 0){
                    cell.style.borderLeft = '2px solid var(--color-border, #ccc)';
                }

                // Every odd slot = end of an hour, add divider
                if(s % 2 === 1){
                    cell.style.borderRight = '2px solid var(--color-border, #ccc)';
                }


                // Apply initial state from bitmask
                const bit = 1n << BigInt(s);
                if(BigInt(gridData[d]) & bit){
                    cell.classList.add('active');
                    cell.style.background = 'var(--av-active, #4CAF50)';
                    cell.style.borderColor = 'var(--av-active, #4CAF50)';
                }

                // Hover
                cell.addEventListener('mouseenter', function(){
                    if(!this.classList.contains('active')){
                        this.style.background = 'var(--color-bg-hover, rgba(255,255,255,0.1))';
                    }
                });
                cell.addEventListener('mouseleave', function(){
                    if(!this.classList.contains('active')){
                        this.style.background = 'var(--color-bg-raised, transparent)';
                    }
                });

                // Click toggle
                cell.addEventListener('click', function(){
                    const day = parseInt(this.dataset.day);
                    const slot = parseInt(this.dataset.slot);
                    const bit = 1n << BigInt(slot);
                    let mask = BigInt(gridData[day]);
                    if(mask & bit){
                        // Turn off
                        mask &= ~bit;
                        this.classList.remove('active');
                        this.style.background = 'var(--color-bg-raised, transparent)';
                        this.style.borderColor = 'var(--color-border, #ccc)';
                    } else {
                        // Turn on
                        mask |= bit;
                        this.classList.add('active');
                        this.style.background = 'var(--av-active, #4CAF50)';
                        this.style.borderColor = 'var(--av-active, #4CAF50)';
                    }
                    gridData[day] = Number(mask);
                });

                row.appendChild(cell);
            }

            wrapper.appendChild(row);
            
            // Clear button
            const clearBtn = document.createElement('button');
            clearBtn.textContent = 'Clear';
            clearBtn.type = 'button';
            clearBtn.title = 'Clear all slots for ' + DAYS[d];
            clearBtn.style.cssText = 'font-size:9px;padding:1px 4px;margin:0 2px 0 4px;cursor:pointer;border:1px solid var(--color-border,#ccc);border-radius:3px;background:var(--color-bg-raised,transparent);color:var(--color-text,#333);line-height:14px;';
            clearBtn.addEventListener('click', function(e){
                e.stopPropagation();
                const dayCells = row.querySelectorAll('[data-day="' + d + '"]');
                dayCells.forEach(function(cell){
                    cell.classList.remove('active');
                    cell.style.background = 'var(--color-bg-raised, transparent)';
                    cell.style.borderColor = 'var(--color-border, #ccc)';
                });
                gridData[d] = 0;
            });
            row.appendChild(clearBtn);

            // Reset button
            const resetBtn = document.createElement('button');
            resetBtn.textContent = 'Reset';
            resetBtn.title = 'Reset ' + DAYS[d] + ' to saved state';
            resetBtn.type = 'button';
            resetBtn.style.cssText = 'font-size:9px;padding:1px 4px;margin:0 2px;cursor:pointer;border:1px solid var(--color-border,#ccc);border-radius:3px;background:var(--color-bg-raised,transparent);color:var(--color-text,#333);line-height:14px;';
            resetBtn.addEventListener('click', function(e){
                e.stopPropagation();
                gridData[d] = originalData[d];
                const dayCells = row.querySelectorAll('[data-day="' + d + '"]');
                dayCells.forEach(function(cell){
                    const slot = parseInt(cell.dataset.slot);
                    const bit = 1n << BigInt(slot);
                    if(BigInt(originalData[d]) & bit){
                        cell.classList.add('active');
                        cell.style.background = 'var(--av-active, #4CAF50)';
                        cell.style.borderColor = 'var(--av-active, #4CAF50)';
                    } else {
                        cell.classList.remove('active');
                        cell.style.background = 'var(--color-bg-raised, transparent)';
                        cell.style.borderColor = 'var(--color-border, #ccc)';
                    }
                });
            });
            row.appendChild(resetBtn);

        }

        container.appendChild(wrapper);
    }

    // ── Apply / Save ──
    function saveAvailability(){
        const serialized = serializeAvailability(gridData);
        document.getElementById('av-data').value = serialized;
        document.getElementById('av-form').submit();
    }

    // ── Export to global scope ──
    window.AvailabilityGrid = {
        init: function(containerId, initialStr){
            const data = parseAvailability(initialStr);
            buildGrid(containerId, data);
            // Wire up the Apply button
            const btn = document.getElementById('av-apply-btn');
            if(btn){
                btn.addEventListener('click', saveAvailability);
            }
        }
    };
})();
