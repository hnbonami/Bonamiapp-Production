<script>
// Voeg body measurements toe aan de results pagina
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.href.includes('/results') && typeof body_measurements !== 'undefined') {
        const container = document.querySelector('.container');
        if (container) {
            const bodyMeasurementsDiv = document.createElement('div');
            bodyMeasurementsDiv.innerHTML = `
                <hr style="margin: 30px 0;">
                <div class="body-measurements-section">
                    <h3>Lichaamsmaten en afmetingen</h3>
                    <div class="measurements-container" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="measurements-table" style="flex: 1;">
                            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                                <thead>
                                    <tr style="background-color: #f5f5f5;">
                                        <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Meting</th>
                                        <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Waarde</th>
                                    </tr>
                                </thead>
                                <tbody id="measurements-body">
                                </tbody>
                            </table>
                        </div>
                        <div class="body-diagram" style="flex: 1; text-align: center; padding: 20px; border: 1px solid #ddd; background-color: #f9f9f9;">
                            <h4>Lichaamsmaten diagram</h4>
                            <div style="width: 200px; height: 300px; margin: 0 auto; background-color: #e9e9e9; border: 2px solid #ccc; position: relative; border-radius: 10px;">
                                <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); font-size: 12px;">Placeholder diagram</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(bodyMeasurementsDiv);
        }
    }
});
</script>