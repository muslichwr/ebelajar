<?php

require_once('../../config.php');

global $DB;

redirect_if_major_upgrade_required();

require_login();

$group_project = $_GET['kelompok'];
$cmid = $_GET['id'];

$groups_number = $DB->get_field('groupproject', 'group_number', ['id' => $group_project]);

// Mengambil data project berdasarkan kelompok
$groups = $DB->get_records('groupstudentproject', array('groupproject' => $group_project));
$ebelajar_records = $DB->get_record('ebelajar', ['coursemoduleid' => $cmid]);

$student_records = $DB->get_records('groupstudentproject', ['groupproject' => $group_project]);

if ($group_project) {
    $project_data = $DB->get_record('project', ['group_project' => $group_project]);
    $step = $DB->get_record('project', ['group_project' => $group_project]);
    
    // Fetch indicators for monitoring
    $indicators = [];
    $total_indicators = 0;
    $invalid_count = 0;
    $low_refs_count = 0;
    
    if ($project_data && isset($project_data->id)) {
        $indicators = $DB->get_records('project_indicators', 
            ['project_id' => $project_data->id], 
            'created_at ASC'
        );
        
        $total_indicators = count($indicators);
        foreach ($indicators as $ind) {
            if ($ind->is_valid == 0) $invalid_count++;
            
            $refs = json_decode($ind->references, true);
            if (!is_array($refs) || count($refs) < 3) {
                $low_refs_count++;
            }
        }
    }
    $step1_status = $DB->get_field('project', 'status_step1', ['group_project' => $group_project]);
    $step2_status = $DB->get_field('project', 'status_step2', ['group_project' => $group_project]);
    $step3_status = $DB->get_field('project', 'status_step3', ['group_project' => $group_project]);
    $step4_status = $DB->get_field('project', 'status_step4', ['group_project' => $group_project]);
    $step5_status = $DB->get_field('project', 'status_step5', ['group_project' => $group_project]);
    $step6_status = $DB->get_field('project', 'status_step6', ['group_project' => $group_project]);
} else {
    $project_data = null;
}

echo '
<style>
    :root {
        --custom-blue: #bed4d1;
        --custom-green: #5a9f68;
        --custom-red: #ff5757;
    }
    .logo {
        display: inline-block;
    }
</style>
';

echo '
    <nav class="container navbar navbar-expand-md navbar-light px-2 rounded-2" style="background-color: var(--custom-green); border: 4px solid var(--custom-blue);">
        <div class="container-fluid px-md-5">
            <div class="logo mx-auto">
                <!-- <img class="navbar-brand" src="path-to-your-logo.png" alt="Logo" width="100"> -->
                <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
            </div>
        </div>
    </nav>
';

echo '
    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <div class="col-lg-6 col-12 text-center mb-4 mb-lg-0">
                <img src="assets/guru-awal(3).svg" class="img-fluid" alt="Ilustrasi">
            </div>
            <div class="col-lg-6 col-12 text-center">
                <h1 class="fw-bolder text-white"><strong>KELOMPOK ' . htmlspecialchars($groups_number) . '</strong></h1>
            </div>
        </div>
    </div>
';

echo' 
    <div class="container mx-auto p-3">
        <div class="d-flex justify-content-start">
            <button class="btn d-flex align-items-center fw-bold" onclick="kembalimenu1('.$cmid.')" style="background-color: var(--custom-blue); color: var(--custom-red);">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </button>
        </div>
    </div>
';

if ($student_records) {
    // Get current leader
    $leader = $DB->get_record('groupstudentproject', [
        'groupproject' => $group_project,
        'is_leader' => 1
    ]);
    
    echo '
    <div class="container mx-auto p-3">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: var(--custom-green);">
                <h4>Kelompok ' . htmlspecialchars($groups_number) . '</h4>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalSetupGuru">
                    <i class="fas fa-cog"></i> Atur Kelompok
                </button>
            </div>
            <div class="card-body" style="background-color: var(--custom-blue);">';
    
    // Show current leader
    if ($leader) {
        echo '<div class="alert alert-success">
                <strong><i class="fas fa-crown"></i> Ketua Kelompok:</strong> ' . htmlspecialchars($leader->name_student) . '
              </div>';
    } else {
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Belum ada ketua kelompok. Silakan atur ketua kelompok.
              </div>';
    }
    
    echo '
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody>
    ';

    $no = 1;

    foreach ($student_records as $record) {
        $user_id = intval($record->user_id);

        $user = $DB->get_record('user', ['id' => $user_id], 'firstname, lastname');

        if ($user) {
            $nama_user = htmlspecialchars($user->firstname . ' ' . $user->lastname);
        } else {
            $nama_user = 'Nama tidak tersedia';
        }

        echo '
            <tr>
                <td>' . $no++ . '.</td>
                <td>' . $nama_user . '</td>
            </tr>
        ';
    }

    echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    ';
} else {
    echo '
    <div class="container mx-auto p-3">
        <div class="alert alert-warning">
            Tidak ada data siswa dalam kelompok ini.
        </div>
    </div>
    ';
}

echo '
<div class="container mx-auto p-3" id="dataStep1">
    <div class="row">
        <div class="col-12">' .
            ($step1_status == "Selesai" ? 
                '<h3>Tahap 1 - Progress Monitoring</h3>
                
                <!-- Progress Cards -->
                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h2 class="card-title text-primary mb-0">' . $total_indicators . '</h2>
                                <p class="card-text small text-muted">Total Indikator</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100 ' . ($invalid_count > 0 ? 'border-warning' : 'border-success') . '">
                            <div class="card-body">
                                <h2 class="card-title mb-0 ' . ($invalid_count > 0 ? 'text-warning' : 'text-success') . '">' . $invalid_count . '</h2>
                                <p class="card-text small text-muted">Tidak Terbukti</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100 ' . ($low_refs_count > 0 ? 'border-danger' : 'border-success') . '">
                            <div class="card-body">
                                <h2 class="card-title mb-0 ' . ($low_refs_count > 0 ? 'text-danger' : 'text-success') . '">' . $low_refs_count . '</h2>
                                <p class="card-text small text-muted">Referensi Kurang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4><i class="fas fa-lightbulb"></i> Detail Tahap 1</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <div class="mb-3 p-3 bg-white rounded">
                            <p class="mb-2"><strong><i class="fas fa-book-open"></i> Studi Kasus:</strong></p>
                            <p class="mb-0">' . nl2br(htmlspecialchars($ebelajar_records->case_study)) . '</p>
                        </div>
                        <div class="mb-3 p-3 bg-white rounded">
                            <p class="mb-2"><strong><i class="fas fa-question-circle"></i> Rumusan Masalah:</strong></p>
                            <p class="mb-0">' . 
                                (!empty($step->step1_formulation) ? 
                                    nl2br(htmlspecialchars($step->step1_formulation)) : 
                                    '<span class="badge bg-warning">Belum diisi</span>'
                                ) . 
                            '</p>
                        </div>
                        
                        ' . (!empty($indicators) ? 
                            '<div class="mt-3">
                                <h6><i class="fas fa-list-check"></i> Indikator (' . count($indicators) . '):</h6>
                                <div class="accordion" id="accordionIndicators">'
                                <div class="accordion" id="accordionIndicators">';
                                
                                $acc_counter = 0;
                                foreach ($indicators as $ind) {
                                    $acc_counter++;
                                    $refs = json_decode($ind->references, true);
                                    $refs_count = is_array($refs) ? count($refs) : 0;
                                    $is_invalid = ($ind->is_valid == 0);
                                    
                                    echo '
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading' . $acc_counter . '">
                                            <button class="accordion-button collapsed ' . ($is_invalid ? 'text-muted' : '') . '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $acc_counter . '">
                                                <strong>' . $acc_counter . '. ' . htmlspecialchars($ind->indicator_name) . '</strong>
                                                <span class="ms-auto me-2 badge ' . ($is_invalid ? 'bg-secondary' : 'bg-success') . '">' . ($is_invalid ? 'Tidak Terbukti' : 'Terbukti') . '</span>
                                                ' . ($refs_count < 3 ? '<span class="badge bg-danger">' . $refs_count . ' refs</span>' : '<span class="badge bg-success">' . $refs_count . ' refs</span>') . '
                                            </button>
                                        </h2>
                                        <div id="collapse' . $acc_counter . '" class="accordion-collapse collapse" data-bs-parent="#accordionIndicators">
                                            <div class="accordion-body bg-light">
                                                <p><strong>Analisis:</strong></p>
                                                <p>' . nl2br(htmlspecialchars($ind->analysis)) . '</p>
                                                <p><strong>Referensi (' . $refs_count . '):</strong></p>
                                                <ol>';
                                                    if (is_array($refs) && count($refs) > 0) {
                                                        foreach ($refs as $ref) {
                                                            echo '<li class="mb-1"><a href="' . htmlspecialchars($ref) . '" target="_blank" class="text-decoration-none">' . htmlspecialchars($ref) . ' <i class="fas fa-external-link-alt fa-xs"></i></a></li>';
                                                        }
                                                    } else {
                                                        echo '<li class="text-muted">Tidak ada referensi</li>';
                                                    }
                                                echo '</ol>
                                            </div>
                                        </div>
                                    </div>';
                                }
                                
                            echo '</div>
                            </div>'
                        : '<div class="alert alert-info mt-3"><i class="fas fa-info-circle"></i> Belum ada indikator yang ditambahkan.</div>') . '
                    </div>
                </div>' : 
                '<h3>Tahap 1</h3>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Kelompok ini belum menyelesaikan tahap 1.
                </div>'
            ) . 
        '</div>
    </div>
</div>';

echo '
<div class="container mx-auto p-3" id="dataStep2">
    <div class="row">
        <div class="col-12">' .
            ($step2_status == "Selesai" ? 
                '<h3>Tahap 2</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Penyusunan Indikator</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                        <p><strong>file:</strong> ' .
                            (!empty($step->step2_pondation) ? 
                                '<a href="' . (new moodle_url('/mod/ebelajar/' . $step->step2_pondation))->out() . '" download>' . basename($step->step2_pondation) . '</a>' : 
                                '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompok anda!</span>'
                            ) . 
                        '</p>
                    </div>
                </div>' : 
            ($step2_status == "Mengerjakan" ? 
                '<h3>Tahap 2</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Penyusunan Indikator</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                        <p><strong>file:</strong> ' .
                            (!empty($step->step2_pondation) ? 
                                $step->step2_pondation : 
                                '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompok anda!</span>'
                            ) . 
                        '</p>
                    </div>
                </div>' :
                '<h3>Tahap 2</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 2.
                </div>'
            )) . 
        '</div>
    </div>
</div>';

if ($step4_status == "Selesai") {
    foreach ($groups as $group) {
        $user_id = $group->user_id;
    
        $user = $DB->get_record('user', array('id' => $user_id), 'firstname, lastname');
    
        if ($user) {
            $nama_user = htmlspecialchars($user->firstname . ' ' . $user->lastname);
        } else {
            $nama_user = 'Nama tidak tersedia';
        }
        echo '
        <div class="container mx-auto p-3">
            <h3>Tahap 4</h3>
            <div class="row align-items-center rounded-2 p-3"  style="background-color: var(--custom-blue);">
                <!-- Kolom teks -->
                <div class="col-6 col-sm-9 text-start rounded-left">
                    <div class="d-flex align-items-center px-1 px-md-3 py-2">
                        <h4 class="fw-bold" style="color: var(--custom-green);">'. $nama_user .'</h4>
                    </div>
                </div>
                <!-- Kolom gambar -->
                <div class="col-6 col-sm-3 text-center rounded-right">
                    <div class="border-0 border-dark px-1 px-md-3 py-2 w-125 text-center">
                        <button class="btn text-white" style="background-color: var(--custom-red);" onclick="lihatDetail(' . $group_project . ', ' . $user_id . ', ' . $cmid . ')">Lihat Siswa</button>
                    </div>
                </div>
            </div>
        </div>
        ';
    }
} elseif ($step4_status == "Mengerjakan") {
    echo '
    <div class="container mx-auto p-3" id="dataStep2">
        <div class="row">
            <div class="col-12">
                <h3>Tahap 4</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menambahkan aktivitasnya sama sekali. Jikalau dibutuhkan segera silahkan hubungi siswa.
                </div>
            </div>
        </div>
    </div>
    ';
} else {
    echo '
    <div class="container mx-auto p-3" id="dataStep2">
        <div class="row">
            <div class="col-12">
                <h3>Tahap 4</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 4.
                </div>
            </div>
        </div>
    </div>
    ';
}

echo '
<div class="container mx-auto p-3" id="dataStep5">
    <div class="row">
        <div class="col-12">' .
            ($project_data && $step5_status == "Selesai" ? 
                '<h3>Tahap 5</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Data Project Kelompok Anda</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Judul Project:</strong> ' . $project_data->title_project . '</p>
                        <p><strong>Deskripsi Project:</strong> ' . $project_data->description_project . '</p>' .
                        (!empty($project_data->file_path) ? 
                            '<p><strong>File:</strong> <a href="' . $project_data->file_path . '" download>' . basename($project_data->file_path) . '</a></p>' : 
                            '<p><strong>File:</strong> Tidak ada file yang diunggah.</p>'
                        ) . 
                    '</div>
                </div>' :
            ($step5_status == "Mengerjakan" ? 
                '<h3>Tahap 5</h3>
                <div class="alert alert-warning">
                    Kelompok mu belum menambahkan data project. Yuk jikalau sudah selesai silahkan dikumpulkan.
                </div>' :
                '<h3>Tahap 5</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 5.
                </div>'
            )) . 
        '</div>
    </div>
</div>';

echo '
<div class="container mx-auto p-3" id="dataStep6">
    <div class="row">
        <div class="col-12">' .
            ($project_data && $step6_status == "Selesai" ? 
                '<h3>Tahap 6</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Data Evaluasi Kelompokmu</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Evaluasi:</strong> ' . $project_data->evaluation . '</p>
                    </div>
                </div>' :
            ($step6_status == "Mengerjakan" ? 
                '<h3>Tahap 6</h3>
                <div class="alert alert-warning">
                    Kelompok mu belum menambahkan evaluasi. Yuk jikalau sudah selesai silahkan dikumpulkan.
                </div>' :
                '<h3>Tahap 6</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 6.
                </div>'
            )) . 
        '</div>
    </div>
</div>';


?>

<!-- Modal Setup Guru -->
<div class="modal fade" id="modalSetupGuru" tabindex="-1" aria-labelledby="modalSetupGuruLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                <h5 class="modal-title" id="modalSetupGuruLabel">Atur Kelompok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSetupGuru">
                    <input type="hidden" name="group_project_id" value="<?php echo $group_project; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                    
                    <div class="mb-3">
                        <label for="leader_user_id" class="form-label"><strong>Pilih Ketua Kelompok</strong></label>
                        <select name="leader_user_id" id="leader_user_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php
                            foreach ($student_records as $record) {
                                $user = $DB->get_record('user', ['id' => $record->user_id], 'firstname, lastname');
                                if ($user) {
                                    $selected = ($record->is_leader == 1) ? 'selected' : '';
                                    echo '<option value="' . $record->user_id . '" ' . $selected . '>' . 
                                         htmlspecialchars($user->firstname . ' ' . $user->lastname) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Pilih siswa yang akan menjadi ketua kelompok. 
                            Idealnya pilih dari Top 10 ranking akademik.
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="teacher_scenario" class="form-label"><strong>Skenario Khusus (Opsional)</strong></label>
                        <textarea name="teacher_scenario" id="teacher_scenario" class="form-control" rows="4" 
                                  placeholder="Tulis skenario kasus spesifik untuk kelompok ini (kosongkan jika ingin menggunakan studi kasus umum)"><?php 
                            echo htmlspecialchars($ebelajar_records->teacher_scenario ?? ''); 
                        ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn" style="background-color: var(--custom-green); color:#ffffff" id="btnSaveSetup">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<!-- Script akan diinisialisasi dari logicscript.php setelah AJAX load -->
<script>
// Flag untuk menandai bahwa modal sudah dimuat
window.modalSetupGuruLoaded = true;
</script>


