/**
 * Nepali (Bikram Sambat) Datepicker — dependency-free, offline-capable.
 *
 * AD<->BS calendar table (BS year 2000 - 2090) is transcribed exactly from
 * the ernilambar/nepali-date composer package (vendor/ernilambar/nepali-date)
 * so that JS conversions always match the backend adToBs()/bsToAd() helpers.
 *
 * Usage: attach class "bs-date-display" to a readonly text input, and give
 * it data-ad-target="<id of a hidden input holding the real AD value>".
 * See resources/views/partials/bs-date-input.blade.php.
 */
(function () {
    'use strict';

    // BS_DATA[i] = [0, m1len, m2len, ..., m12len] for BS year (2000 + i)
    var BS_DATA = [
  [0,30,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,32,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,31,29,30,30,29,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,32,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,30,32,31,32,31,31,29,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,29,30,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,31,32,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,29,30,30],
  [0,31,32,31,32,31,30,30,30,29,30,29,31],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,31,30,29,30,29,30,30],
  [0,31,31,32,31,31,30,30,30,29,30,30,30],
  [0,31,32,31,32,30,31,30,30,29,30,30,30],
  [0,30,32,31,32,31,30,30,30,29,30,30,30],
  [0,31,31,32,31,31,31,30,30,29,30,30,30],
  [0,30,31,32,32,30,31,30,30,29,30,30,30],
  [0,30,32,31,32,31,30,30,30,29,30,30,30],
  [0,30,32,31,32,31,30,30,30,29,30,30,30]
];

    var BS_YEAR_START = 2000;

    var NEP_MONTHS = ['बैशाख', 'जेठ', 'असार', 'श्रावण', 'भदौ', 'असोज', 'कार्तिक', 'मंसिर', 'पौष', 'माघ', 'फाल्गुन', 'चैत'];
    var NEP_DIGITS = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

    function toNepDigits(num) {
        return String(num).split('').map(function (ch) {
            return /[0-9]/.test(ch) ? NEP_DIGITS[ch] : ch;
        }).join('');
    }

    function isLeapYear(y) {
        if (y % 100 === 0) {
            return y % 400 === 0;
        }
        return y % 4 === 0;
    }

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    // Port of NepaliCalendar::convertEnglishToNepali() — 1:1 with the PHP algorithm.
    function adToBs(yy, mm, dd) {
        var month = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var lmonth = [0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        var defEyy = 1944, defNyy = 2000, defNmm = 9, defNdd = 16;
        var totalEdays = 0, totalNdays, day = 6, m, y, i, j;

        for (i = 0; i < (yy - defEyy); i++) {
            if (isLeapYear(defEyy + i)) {
                for (j = 1; j <= 12; j++) totalEdays += lmonth[j];
            } else {
                for (j = 1; j <= 12; j++) totalEdays += month[j];
            }
        }
        for (i = 0; i < (mm - 1); i++) {
            totalEdays += isLeapYear(yy) ? lmonth[i + 1] : month[i + 1];
        }
        totalEdays += dd;

        i = 0; j = defNmm; totalNdays = defNdd; m = defNmm; y = defNyy;

        while (totalEdays !== 0) {
            var a = BS_DATA[i][j];
            totalNdays++;
            day++;
            if (totalNdays > a) {
                m++;
                totalNdays = 1;
                j++;
            }
            if (day > 7) day = 1;
            if (m > 12) { y++; m = 1; }
            if (j > 12) { j = 1; i++; }
            totalEdays--;
        }

        return { year: y, month: m, day: totalNdays };
    }

    // Port of NepaliCalendar::convertNepaliToEnglish() — 1:1 with the PHP algorithm.
    function bsToAd(yy, mm, dd) {
        var month = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var lmonth = [0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        var defEyy = 1943, defEmm = 4, defEdd = 13, defNyy = 2000;
        var totalNdays = 0, k = 0, i, j;

        for (i = 0; i < (yy - defNyy); i++) {
            for (j = 1; j <= 12; j++) totalNdays += BS_DATA[k][j];
            k++;
        }
        for (j = 1; j < mm; j++) totalNdays += BS_DATA[k][j];
        totalNdays += dd;

        var totalEdays = defEdd, m = defEmm, y = defEyy, day = 3;

        while (totalNdays !== 0) {
            var a = isLeapYear(y) ? lmonth[m] : month[m];
            totalEdays++;
            day++;
            if (totalEdays > a) {
                m++;
                totalEdays = 1;
                if (m > 12) { y++; m = 1; }
            }
            if (day > 7) day = 1;
            totalNdays--;
        }

        return { year: y, month: m, day: totalEdays };
    }

    function daysInBsMonth(bsYear, bsMonth) {
        var idx = bsYear - BS_YEAR_START;
        if (idx < 0 || idx >= BS_DATA.length) return 30;
        return BS_DATA[idx][bsMonth];
    }

    function formatBs(d) {
        return d.year + '-' + pad2(d.month) + '-' + pad2(d.day);
    }

    function formatAd(d) {
        return d.year + '-' + pad2(d.month) + '-' + pad2(d.day);
    }

    function parseYmd(str) {
        if (!str) return null;
        var parts = str.split('-');
        if (parts.length !== 3) return null;
        return { year: parseInt(parts[0], 10), month: parseInt(parts[1], 10), day: parseInt(parts[2], 10) };
    }

    // ---- Calendar popup UI ----

    var activePopup = null;

    function closePopup() {
        if (activePopup) {
            activePopup.remove();
            activePopup = null;
        }
    }

    document.addEventListener('click', function (e) {
        if (activePopup && !activePopup.contains(e.target) && !e.target.classList.contains('bs-date-display')) {
            closePopup();
        }
    });

    function openPopup(displayInput) {
        closePopup();

        var hiddenInput = document.getElementById(displayInput.getAttribute('data-ad-target'));
        var current = parseYmd(displayInput.value) || (function () {
            var todayAd = new Date();
            return adToBs(todayAd.getFullYear(), todayAd.getMonth() + 1, todayAd.getDate());
        })();

        var viewYear = current.year;
        var viewMonth = current.month;

        var popup = document.createElement('div');
        popup.className = 'bs-datepicker-popup';
        popup.style.cssText = 'position:absolute;z-index:9999;background:#fff;border:1px solid #cbd5e1;border-radius:0.5rem;box-shadow:0 10px 25px rgba(0,0,0,0.12);padding:0.75rem;width:260px;font-size:0.8rem;';

        // महत्त्वपूर्ण: Prev/Next month click गर्दा render() ले पुरानो button
        // हटाएर नयाँ बनाउँछ (innerHTML = ''), र त्यसपछि click event document
        // सम्म bubble हुँदा "बाहिर click भयो" भन्ने listener ले त्यो
        // (अहिले detach भइसकेको) button लाई popup बाहिरको ठान्छ र
        // closePopup() तुरुन्तै चलाइदिन्छ। यसैले Prev/Next/आज कुनै पनि
        // button ले काम नगरे जस्तो (popup झट्टै बन्द भए जस्तो) देखिन्थ्यो।
        // popup भित्रको कुनै पनि click बाहिर नजाओस् भनेर रोक्ने:
        popup.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        function render() {
            popup.innerHTML = '';

            var header = document.createElement('div');
            header.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;gap:4px;';

            var prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.textContent = '‹';
            prevBtn.style.cssText = 'border:none;background:#f1f5f9;border-radius:0.25rem;width:24px;height:24px;cursor:pointer;';
            prevBtn.onclick = function () {
                viewMonth--;
                if (viewMonth < 1) { viewMonth = 12; viewYear--; }
                render();
            };

            var label = document.createElement('div');
            label.style.cssText = 'display:flex;align-items:center;gap:4px;font-weight:600;color:#334155;';

            var monthSpan = document.createElement('span');
            monthSpan.textContent = NEP_MONTHS[viewMonth - 1];
            label.appendChild(monthSpan);

            var yearSelect = document.createElement('select');
            yearSelect.style.cssText = 'font-weight:600;color:#334155;border:none;background:transparent;cursor:pointer;font-size:0.8rem;';
            for (var yy = BS_YEAR_START; yy < BS_YEAR_START + BS_DATA.length; yy++) {
                var opt = document.createElement('option');
                opt.value = yy;
                opt.textContent = yy;
                if (yy === viewYear) opt.selected = true;
                yearSelect.appendChild(opt);
            }
            yearSelect.onchange = function () {
                viewYear = parseInt(this.value, 10);
                render();
            };
            label.appendChild(yearSelect);

            var nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.textContent = '›';
            nextBtn.style.cssText = 'border:none;background:#f1f5f9;border-radius:0.25rem;width:24px;height:24px;cursor:pointer;';
            nextBtn.onclick = function () {
                viewMonth++;
                if (viewMonth > 12) { viewMonth = 1; viewYear++; }
                render();
            };

            header.appendChild(prevBtn);
            header.appendChild(label);
            header.appendChild(nextBtn);
            popup.appendChild(header);

            var grid = document.createElement('div');
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;';

            ['आ', 'सो', 'मं', 'बु', 'बि', 'शु', 'श'].forEach(function (d) {
                var cell = document.createElement('div');
                cell.textContent = d;
                cell.style.cssText = 'color:#94a3b8;font-size:0.7rem;padding:2px 0;';
                grid.appendChild(cell);
            });

            var totalDays = daysInBsMonth(viewYear, viewMonth);
            var firstAd = bsToAd(viewYear, viewMonth, 1);
            var firstWeekday = new Date(firstAd.year, firstAd.month - 1, firstAd.day).getDay(); // 0=Sun

            for (var s = 0; s < firstWeekday; s++) {
                grid.appendChild(document.createElement('div'));
            }

            for (var day = 1; day <= totalDays; day++) {
                (function (day) {
                    var cell = document.createElement('button');
                    cell.type = 'button';
                    cell.textContent = toNepDigits(day);
                    var isSelected = current.year === viewYear && current.month === viewMonth && current.day === day;
                    cell.style.cssText = 'border:none;border-radius:0.3rem;padding:4px 0;cursor:pointer;background:' +
                        (isSelected ? '#7c3aed' : 'transparent') + ';color:' + (isSelected ? '#fff' : '#334155') + ';';
                    cell.onmouseenter = function () { if (!isSelected) cell.style.background = '#f1f5f9'; };
                    cell.onmouseleave = function () { if (!isSelected) cell.style.background = 'transparent'; };
                    cell.onclick = function () {
                        var bs = { year: viewYear, month: viewMonth, day: day };
                        var ad = bsToAd(viewYear, viewMonth, day);
                        displayInput.value = formatBs(bs);
                        if (hiddenInput) {
                            hiddenInput.value = formatAd(ad);
                            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        closePopup();
                    };
                    grid.appendChild(cell);
                })(day);
            }

            popup.appendChild(grid);

            var todayBtn = document.createElement('button');
            todayBtn.type = 'button';
            todayBtn.textContent = 'आज';
            todayBtn.style.cssText = 'margin-top:0.5rem;width:100%;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;border-radius:0.375rem;padding:4px 0;font-size:0.75rem;cursor:pointer;';
            todayBtn.onclick = function () {
                var todayAd = new Date();
                var bs = adToBs(todayAd.getFullYear(), todayAd.getMonth() + 1, todayAd.getDate());
                displayInput.value = formatBs(bs);
                if (hiddenInput) {
                    hiddenInput.value = todayAd.getFullYear() + '-' + pad2(todayAd.getMonth() + 1) + '-' + pad2(todayAd.getDate());
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                closePopup();
            };
            popup.appendChild(todayBtn);
        }

        render();

        displayInput.parentNode.style.position = 'relative';
        displayInput.parentNode.appendChild(popup);
        popup.style.top = (displayInput.offsetTop + displayInput.offsetHeight + 4) + 'px';
        popup.style.left = '0px';

        activePopup = popup;
    }

    function initDisplay(displayInput) {
        if (displayInput.dataset.bsInit === '1') return;
        displayInput.dataset.bsInit = '1';

        var hiddenInput = document.getElementById(displayInput.getAttribute('data-ad-target'));

        if (hiddenInput && hiddenInput.value) {
            var ad = parseYmd(hiddenInput.value);
            if (ad) {
                displayInput.value = formatBs(adToBs(ad.year, ad.month, ad.day));
            }
        }

        displayInput.addEventListener('click', function () {
            openPopup(displayInput);
        });
    }

    function initAll() {
        document.querySelectorAll('.bs-date-display').forEach(initDisplay);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    window.initBsDatepickers = initAll;
    window.NepaliDateUtil = { adToBs: adToBs, bsToAd: bsToAd };
})();
