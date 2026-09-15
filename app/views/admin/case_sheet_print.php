<?php
    /** @var array $sheet */
    $row = static function (string $label, string $value): string {
        return '<tr><td class="lbl">' . e($label) . '</td><td>' . e($value) . '</td></tr>';
    };
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Fișă de caz · Salvamont Zărnești</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #222; margin: 2rem; }
        header { display: flex; align-items: center; gap: 1rem; border-bottom: 3px solid #b3202c; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        header img { height: 60px; }
        h1 { font-size: 1.3rem; margin: 0; }
        .meta { color: #555; font-size: .85rem; margin-bottom: 1.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; margin-bottom: 1rem; }
        td { border: 1px solid #999; padding: .4rem .6rem; vertical-align: top; }
        td.lbl { background: #f2ece9; font-weight: bold; width: 38%; }
        .section td { background: #841821; color: #fff; font-weight: bold; text-transform: uppercase; font-size: .8rem; letter-spacing: .03em; }
        .no-print { margin-bottom: 1.5rem; }
        /* Evită tăierea unui rând/secțiune la trecerea dintre pagini la print. */
        tr { page-break-inside: avoid; break-inside: avoid; }
        thead { display: table-header-group; }
        .section td { page-break-after: avoid; break-after: avoid; }
        @media print {
            .no-print { display: none; }
            body { margin: 0.6cm; }
            table { font-size: .82rem; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Printează / Salvează ca PDF</button>
        <a href="<?= route_url('?route=admin/case-sheets') ?>" style="margin-left:.5rem;">Înapoi la listă</a>
    </div>

    <header>
        <img src="<?= logo_url() ?>" alt="Salvamont Zărnești">
        <div>
            <h1>Fișă de caz</h1>
            <div><?= e($sheet['serviciu'] ?: 'Salvamont Zărnești') ?></div>
        </div>
    </header>

    <div class="meta">
        <div><strong>Nr. fișă:</strong> <?= e($sheet['nr_fisa'] ?: '—') ?>
            &nbsp;&middot;&nbsp;
            <strong>Data:</strong> <?= e($sheet['data_fisa'] ?: '—') ?>
        </div>
        <div><strong>Generat la:</strong> <?= date('d.m.Y H:i') ?></div>
    </div>

    <table>
        <?= $row('Județ', $sheet['judet']) ?>
        <?= $row('Serviciu', $sheet['serviciu']) ?>
        <?= $row('Formație', $sheet['formatie']) ?>
        <tr class="section"><td colspan="2">Date despre alarmarea inițială</td></tr>
        <?= $row('Alarmare prin', $sheet['alarmare_prin']) ?>
        <?= $row('Data / Ora', $sheet['alarmare_datetime']) ?>
        <?= $row('Tip eveniment', $sheet['tip_eveniment']) ?>
        <?= $row('Responsabilitate', $sheet['responsabilitate']) ?>
        <?= $row('Intervenție', $sheet['interventie']) ?>
        <?= $row('Coordonator intervenție', $sheet['coordonator']) ?>
        <?= $row('Salvatori', $sheet['salvatori']) ?>
        <tr class="section"><td colspan="2">Locație</td></tr>
        <?= $row('Județ', $sheet['locatie_judet']) ?>
        <?= $row('Masiv montan', $sheet['masiv_montan']) ?>
        <?= $row('Sezon', $sheet['sezon']) ?>
        <?= $row('Loc producere', $sheet['loc_producere']) ?>
        <?= $row('Tip de activitate generatoare de eveniment', $sheet['tip_activitate']) ?>
        <?= $row('Număr de persoane implicate', $sheet['numar_persoane']) ?>
        <tr class="section"><td colspan="2">Date de identificare victimă</td></tr>
        <?= $row('Nume și prenume', $sheet['victima_nume']) ?>
        <?= $row('Vârstă', $sheet['victima_varsta']) ?>
        <?= $row('Sex', $sheet['victima_sex']) ?>
        <?= $row('Județ', $sheet['victima_judet']) ?>
        <?= $row('Țară', $sheet['victima_tara']) ?>
        <?= $row('Stare pacient', $sheet['victima_stare']) ?>
        <?= $row('Contact victimă', $sheet['victima_contact']) ?>
        <?= $row('Localizare afecțiune medicală', $sheet['localizare_afectiune']) ?>
        <?= $row('Finalitate caz', $sheet['finalitate_caz']) ?>
        <tr class="section"><td colspan="2">Transport și finalizare</td></tr>
        <?= $row('Data și ora predare victimă', $sheet['predare_datetime']) ?>
        <?= $row('Transport accidentat', $sheet['transport']) ?>
        <?= $row('Mod evacuare victimă', $sheet['mod_evacuare']) ?>
        <?= $row('Predată către', $sheet['predata_catre']) ?>
        <?= $row('Eveniment cu victime multiple', $sheet['victime_multiple']) ?>
        <?= $row('Data și ora revenire bază Salvamont', $sheet['revenire_datetime']) ?>
        <?= $row('Întocmit', $sheet['intocmit']) ?>
    </table>
<?php if (!empty($autoprint)): ?>
    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
<?php endif; ?>
</body>
</html>
