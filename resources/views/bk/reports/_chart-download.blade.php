@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartExportUrl = @json(route('bk.reports.charts.export'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const notifyError = function () {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Export gagal', text: 'Export grafik gagal. Coba lagi.' });
                } else {
                    alert('Export grafik gagal. Coba lagi.');
                }
            };
            const chartPng = function (canvas) {
                const chart = window.Chart ? window.Chart.getChart(canvas) : null;
                const source = chart ? chart.canvas : canvas;
                const offscreen = document.createElement('canvas');
                offscreen.width = source.width;
                offscreen.height = source.height;
                const context = offscreen.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, offscreen.width, offscreen.height);
                context.drawImage(source, 0, 0);
                return offscreen.toDataURL('image/png');
            };
            const cardTitle = function (canvas) {
                const heading = canvas.closest('section')?.querySelector('h2');
                return ((heading ? heading.textContent : canvas.id) || 'grafik').trim();
            };
            const downloadBlob = function (blob, filename) {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(function () { URL.revokeObjectURL(link.href); }, 5000);
            };
            const exportCharts = function (charts) {
                return fetch(chartExportUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/pdf',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ charts: charts }),
                }).then(function (response) {
                    if (!response.ok) throw new Error('Export gagal.');
                    const disposition = response.headers.get('content-disposition') || '';
                    const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                    const filename = match && match[1] ? match[1].replace(/['"]/g, '') : 'grafik-laporan.pdf';
                    return response.blob().then(function (blob) { downloadBlob(blob, filename); });
                });
            };
            const makeDownloadButton = function (canvasId, label) {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('data-download-chart', canvasId);
                button.title = label;
                button.setAttribute('aria-label', label);
                button.className = 'inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100 disabled:opacity-60';
                button.innerHTML = '<i class="fa-solid fa-file-pdf text-xs"></i>';
                return button;
            };
            document.querySelectorAll('canvas[id]').forEach(function (canvas) {
                const card = canvas.closest('section');
                if (!card || card.querySelector('[data-download-chart]')) return;
                const header = card.querySelector(':scope > div.mb-5');
                const button = makeDownloadButton(canvas.id, 'Unduh grafik (PDF)');
                button.addEventListener('click', function () {
                    button.disabled = true;
                    exportCharts([{ title: cardTitle(canvas), image: chartPng(canvas) }])
                        .catch(notifyError)
                        .finally(function () { button.disabled = false; });
                });
                if (header) {
                    header.classList.add('flex', 'items-start', 'justify-between', 'gap-3');
                    let textWrap = header.querySelector(':scope > div');
                    if (!textWrap) {
                        textWrap = document.createElement('div');
                        textWrap.className = 'min-w-0 flex-1';
                        while (header.firstChild) textWrap.appendChild(header.firstChild);
                        header.appendChild(textWrap);
                    }
                    header.appendChild(button);
                } else {
                    card.classList.add('relative');
                    button.classList.add('absolute', 'right-4', 'top-4');
                    card.appendChild(button);
                }
            });
            document.querySelectorAll('[data-export-all-charts]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const charts = Array.from(document.querySelectorAll('canvas[id]')).map(function (canvas) {
                        return { title: cardTitle(canvas), image: chartPng(canvas) };
                    });
                    if (!charts.length) return;
                    button.disabled = true;
                    exportCharts(charts)
                        .catch(notifyError)
                        .finally(function () { button.disabled = false; });
                });
            });
        });
    </script>
@endpush
