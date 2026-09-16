<?php
if (!function_exists('date_ro_display')) {
    function date_ro_display($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if ($d === false) {
            $d = DateTime::createFromFormat('d/m/Y', $value);
        }
        return $d ? $d->format('d/m/Y') : $value;
    }
}
?>
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
                    <div class="input-group date-ro-group">
                        <input type="text" name="data_fisa" class="form-control date-ro" value="<?= e(date_ro_display(old('data_fisa'))) ?>" placeholder="zz/ll/aaaa" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" title="Format: zi/lună/an (zz/ll/aaaa)">
                        <input type="date" class="date-ro-picker" tabindex="-1" aria-label="Alege data">
                    </div>
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
                    <div class="input-group date-ro-group">
                        <input type="text" name="alarmare_data" class="form-control date-ro" value="<?= e(date_ro_display(old('alarmare_data'))) ?>" placeholder="zz/ll/aaaa" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" title="Format: zi/lună/an (zz/ll/aaaa)">
                        <input type="date" class="date-ro-picker" tabindex="-1" aria-label="Alege data">
                    </div>
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

            <div class="p-3 border rounded bg-light mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="create_activities" value="1" id="createActivities" <?= old('create_activities') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="createActivities">Înregistrează automat o activitate pentru salvatorii selectați</label>
                </div>
                <div class="row mt-2" id="createActivitiesOptions">
                    <div class="col-md-6">
                        <label class="form-label">Tip activitate</label>
                        <?php $selType = old('activity_type_id', (string) ($defaultActivityTypeId ?: '')); ?>
                        <select name="activity_type_id" class="form-select">
                            <option value="">— Selectați —</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= (string) $selType === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Activitatea este creată și aprobată pentru fiecare aspirant/salvator selectat, cu data fișei. Membrii administratori sunt ignorați.</div>
                    </div>
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

            <div id="victime-suplimentare-wrap" class="d-none">
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h6 text-uppercase text-muted mb-0">Victime suplimentare</h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-victima">+ Adaugă victimă</button>
                </div>
                <div class="form-text mb-3">Prima victimă este cea completată mai sus. Adăugați aici celelalte victime implicate în eveniment; fiecare are propriile date.</div>
                <div id="victime-list"></div>
            </div>

            <template id="victima-template">
                <div class="victima-item card border mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 mb-0">Victimă <span class="victima-nr"></span></h3>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-victima">Șterge</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nume și prenume</label>
                                <input type="text" name="victime[__INDEX__][nume]" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Vârstă</label>
                                <input type="number" name="victime[__INDEX__][varsta]" min="0" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sex</label>
                                <select name="victime[__INDEX__][sex]" class="form-select">
                                    <option value="">— Selectați —</option>
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Județ</label>
                                <input type="text" name="victime[__INDEX__][judet]" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Țară</label>
                                <input type="text" name="victime[__INDEX__][tara]" class="form-control" value="România">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stare pacient</label>
                                <input type="text" name="victime[__INDEX__][stare]" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact victimă</label>
                                <input type="text" name="victime[__INDEX__][contact]" class="form-control" placeholder="ex. telefon">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Localizare afecțiune medicală</label>
                                <input type="text" name="victime[__INDEX__][localizare_afectiune]" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Finalitate caz</label>
                                <input type="text" name="victime[__INDEX__][finalitate_caz]" class="form-control" placeholder="ex. Refuză tratament">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <hr>
            <h2 class="h6 text-uppercase text-muted mb-3">Transport și finalizare</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data predare victimă</label>
                    <div class="input-group date-ro-group">
                        <input type="text" name="predare_data" class="form-control date-ro" value="<?= e(date_ro_display(old('predare_data'))) ?>" placeholder="zz/ll/aaaa" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" title="Format: zi/lună/an (zz/ll/aaaa)">
                        <input type="date" class="date-ro-picker" tabindex="-1" aria-label="Alege data">
                    </div>
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
                    <div class="input-group date-ro-group">
                        <input type="text" name="revenire_data" class="form-control date-ro" value="<?= e(date_ro_display(old('revenire_data'))) ?>" placeholder="zz/ll/aaaa" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" title="Format: zi/lună/an (zz/ll/aaaa)">
                        <input type="date" class="date-ro-picker" tabindex="-1" aria-label="Alege data">
                    </div>
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

<style>
    /* Butonul de calendar (input date nativ) redus la iconi\u021ba de picker */
    .date-ro-group .date-ro-picker {
        flex: 0 0 auto;
        width: 2.6rem;
        border: 1px solid var(--bs-border-color, #ced4da);
        border-left: 0;
        border-top-right-radius: .375rem;
        border-bottom-right-radius: .375rem;
        background: #fff;
        color: transparent;
        cursor: pointer;
    }
    .date-ro-group .date-ro-picker::-webkit-datetime-edit,
    .date-ro-group .date-ro-picker::-webkit-inner-spin-button,
    .date-ro-group .date-ro-picker::-webkit-clear-button {
        display: none;
    }
    .date-ro-group .date-ro-picker::-webkit-calendar-picker-indicator {
        opacity: 1;
        cursor: pointer;
    }
    .date-ro-group .date-ro {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
</style>
<script>
(function () {
    function toRo(iso) {
        var m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        return m ? m[3] + '/' + m[2] + '/' + m[1] : '';
    }
    function toIso(ro) {
        var m = ro.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        return m ? m[3] + '-' + m[2] + '-' + m[1] : '';
    }

    // Sincronizeaz\u0103 calendarul nativ cu c\u00e2mpul text zz/ll/aaaa.
    document.querySelectorAll('.date-ro-group').forEach(function (group) {
        var text = group.querySelector('input.date-ro');
        var picker = group.querySelector('input.date-ro-picker');
        if (!text || !picker) { return; }
        picker.value = toIso(text.value.trim());
        picker.addEventListener('change', function () {
            if (picker.value) {
                text.value = toRo(picker.value);
            }
        });
        text.addEventListener('change', function () {
            picker.value = toIso(text.value.trim());
        });
    });

    // La trimitere, convertim c\u00e2mpurile de dat\u0103 din zz/ll/aaaa \u00een aaaa-ll-zz
    // pentru a p\u0103stra compatibilitatea cu salvarea pe server.
    var form = document.querySelector('form[action*="case-sheet/create"]');
    if (!form) { return; }
    form.addEventListener('submit', function () {
        form.querySelectorAll('input.date-ro').forEach(function (input) {
            var iso = toIso(input.value.trim());
            if (iso) {
                input.value = iso;
            }
        });
    });
})();
</script>
<script>
(function () {
    var select = document.querySelector('select[name="victime_multiple"]');
    var wrap = document.getElementById('victime-suplimentare-wrap');
    var list = document.getElementById('victime-list');
    var template = document.getElementById('victima-template');
    var addBtn = document.getElementById('add-victima');
    if (!select || !wrap || !list || !template || !addBtn) { return; }

    var counter = 0;

    // Renumerotează victimele suplimentare (prima victimă e cea din secțiunea de sus = #1).
    function renumber() {
        list.querySelectorAll('.victima-item').forEach(function (item, i) {
            var nr = item.querySelector('.victima-nr');
            if (nr) { nr.textContent = String(i + 2); }
        });
    }

    function addVictim() {
        var fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach(function (field) {
            field.name = field.name.replace('__INDEX__', String(counter));
        });
        counter++;
        list.appendChild(fragment);
        renumber();
    }

    list.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.btn-remove-victima');
        if (!btn) { return; }
        var item = btn.closest('.victima-item');
        if (item) { item.remove(); renumber(); }
    });

    addBtn.addEventListener('click', addVictim);

    function toggle() {
        if (select.value === 'Da') {
            wrap.classList.remove('d-none');
            if (list.children.length === 0) { addVictim(); }
        } else {
            wrap.classList.add('d-none');
        }
    }

    select.addEventListener('change', toggle);
    toggle();
})();
</script>
<script>
(function () {
    var check = document.getElementById('createActivities');
    var options = document.getElementById('createActivitiesOptions');
    if (!check || !options) { return; }
    var typeSelect = options.querySelector('select[name="activity_type_id"]');

    function sync() {
        options.style.opacity = check.checked ? '1' : '.5';
        if (typeSelect) { typeSelect.disabled = !check.checked; }
    }

    check.addEventListener('change', sync);
    sync();
})();
</script>
