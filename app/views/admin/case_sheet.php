<h1 class="h3 mb-4">Fișă de caz</h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= route_url('?route=admin/case-sheet/create') ?>" novalidate>
            <?= csrf_field() ?>

            <h2 class="h6 text-uppercase text-muted mb-3">Date generale</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nr. fișă</label>
                    <input type="text" name="nr_fisa" class="form-control" value="<?= e(old('nr_fisa', (string) $nextNumber)) ?>">
                    <div class="form-text">Generat automat, dar poate fi modificat.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data</label>
                    <input type="date" name="data_fisa" class="form-control" value="<?= e(old('data_fisa')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Județ</label>
                    <input type="text" name="judet" class="form-control" value="<?= e(old('judet', 'Brașov')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Serviciu Salvamont</label>
                    <input type="text" name="serviciu" class="form-control" value="<?= e(old('serviciu', 'Salvamont Zărnești')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Formație</label>
                    <input type="text" name="formatie" class="form-control" value="<?= e(old('formatie')) ?>">
                </div>
            </div>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Alarmare inițială</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Alarmare prin</label>
                    <?php $selAlarmare = old('alarmare_prin'); ?>
                    <select name="alarmare_prin" class="form-select">
                        <option value="">— Selectați —</option>
                        <option value="Dispecerat Național Salvamont" <?= $selAlarmare === 'Dispecerat Național Salvamont' ? 'selected' : '' ?>>Dispecerat Național Salvamont</option>
                        <option value="Dispecerat Salvamont Zărnești" <?= $selAlarmare === 'Dispecerat Salvamont Zărnești' ? 'selected' : '' ?>>Dispecerat Salvamont Zărnești</option>
                        <option value="Dispecerat 112" <?= $selAlarmare === 'Dispecerat 112' ? 'selected' : '' ?>>Dispecerat 112</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data</label>
                    <input type="date" name="alarmare_data" class="form-control" value="<?= e(old('alarmare_data')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ora <span class="text-muted">(opțional)</span></label>
                    <input type="time" name="alarmare_ora" class="form-control" value="<?= e(old('alarmare_ora')) ?>">
                </div>
            </div>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Detalii eveniment</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tip eveniment</label>
                    <input type="text" name="tip_eveniment" class="form-control" value="<?= e(old('tip_eveniment')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Responsabilitate</label>
                    <input type="text" name="responsabilitate" class="form-control" value="<?= e(old('responsabilitate')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Intervenție</label>
                    <input type="text" name="interventie" class="form-control" value="<?= e(old('interventie')) ?>" placeholder="ex. Preponderent nocturnă">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Coordonator intervenție</label>
                    <input type="text" name="coordonator" class="form-control" value="<?= e(old('coordonator')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Salvatori</label>
                <div class="row g-1">
                    <?php foreach ($members as $m): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="salvatori[]" value="<?= $m['id'] ?>" id="salvator<?= $m['id'] ?>">
                                <label class="form-check-label" for="salvator<?= $m['id'] ?>"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$members): ?>
                        <div class="col-12 text-muted">Niciun membru activ disponibil.</div>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Locație</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Județ</label>
                    <input type="text" name="locatie_judet" class="form-control" value="<?= e(old('locatie_judet', 'Brașov')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Masiv montan</label>
                    <input type="text" name="masiv_montan" class="form-control" value="<?= e(old('masiv_montan')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sezon</label>
                    <?php $selSezon = old('sezon'); ?>
                    <select name="sezon" class="form-select">
                        <option value="">— Selectați —</option>
                        <option value="Vara" <?= $selSezon === 'Vara' ? 'selected' : '' ?>>Vara</option>
                        <option value="Iarna" <?= $selSezon === 'Iarna' ? 'selected' : '' ?>>Iarna</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loc producere</label>
                    <input type="text" name="loc_producere" class="form-control" value="<?= e(old('loc_producere')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tip de activitate generatoare</label>
                    <input type="text" name="tip_activitate" class="form-control" value="<?= e(old('tip_activitate')) ?>" placeholder="ex. turistică">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Nr. persoane implicate</label>
                    <input type="number" name="numar_persoane" min="0" class="form-control" value="<?= e(old('numar_persoane')) ?>">
                </div>
            </div>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Date de identificare victimă</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nume și prenume</label>
                    <input type="text" name="victima_nume" class="form-control" value="<?= e(old('victima_nume')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vârstă</label>
                    <input type="number" name="victima_varsta" min="0" class="form-control" value="<?= e(old('victima_varsta')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sex</label>
                    <?php $selSex = old('victima_sex'); ?>
                    <select name="victima_sex" class="form-select">
                        <option value="">— Selectați —</option>
                        <option value="M" <?= $selSex === 'M' ? 'selected' : '' ?>>M</option>
                        <option value="F" <?= $selSex === 'F' ? 'selected' : '' ?>>F</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Județ</label>
                    <input type="text" name="victima_judet" class="form-control" value="<?= e(old('victima_judet')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Țară</label>
                    <input type="text" name="victima_tara" class="form-control" value="<?= e(old('victima_tara', 'România')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stare pacient</label>
                    <input type="text" name="victima_stare" class="form-control" value="<?= e(old('victima_stare')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact victimă</label>
                    <input type="text" name="victima_contact" class="form-control" value="<?= e(old('victima_contact')) ?>" placeholder="ex. telefon">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Localizare afecțiune medicală</label>
                    <input type="text" name="localizare_afectiune" class="form-control" value="<?= e(old('localizare_afectiune')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Finalitate caz</label>
                    <input type="text" name="finalitate_caz" class="form-control" value="<?= e(old('finalitate_caz')) ?>" placeholder="ex. Refuză tratament">
                </div>
            </div>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Transport și finalizare</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data predare victimă</label>
                    <input type="date" name="predare_data" class="form-control" value="<?= e(old('predare_data')) ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Ora <span class="text-muted">(opțional)</span></label>
                    <input type="time" name="predare_ora" class="form-control" value="<?= e(old('predare_ora')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport accidentat</label>
                    <input type="text" name="transport" class="form-control" value="<?= e(old('transport')) ?>" placeholder="ex. Pedestru">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Eveniment cu victime multiple</label>
                    <?php $selMult = old('victime_multiple'); ?>
                    <select name="victime_multiple" class="form-select">
                        <option value="">— Selectați —</option>
                        <option value="Nu" <?= $selMult === 'Nu' ? 'selected' : '' ?>>Nu</option>
                        <option value="Da" <?= $selMult === 'Da' ? 'selected' : '' ?>>Da</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mod evacuare victimă</label>
                    <?php $selEvac = old('mod_evacuare'); ?>
                    <select name="mod_evacuare" class="form-select">
                        <option value="">— Selectați —</option>
                        <option value="Aero" <?= $selEvac === 'Aero' ? 'selected' : '' ?>>Aero</option>
                        <option value="Terestru" <?= $selEvac === 'Terestru' ? 'selected' : '' ?>>Terestru</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Predată către</label>
                    <input type="text" name="predata_catre" class="form-control" value="<?= e(old('predata_catre')) ?>" placeholder="ex. SAJ / SMURD / aparținători">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data revenire bază Salvamont</label>
                    <input type="date" name="revenire_data" class="form-control" value="<?= e(old('revenire_data')) ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Ora <span class="text-muted">(opțional)</span></label>
                    <input type="time" name="revenire_ora" class="form-control" value="<?= e(old('revenire_ora')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Întocmit</label>
                    <input type="text" name="intocmit" class="form-control" value="<?= e(old('intocmit', $intocmitDefault)) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvează și generează PDF</button>
            <a href="<?= route_url('?route=admin/case-sheets') ?>" class="btn btn-outline-secondary">Anulează</a>
        </form>
    </div>
</div>
