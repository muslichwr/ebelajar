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
    echo '
    <div class="container mx-auto p-3">
        <div class="card">
            <div class="card-header text-white" style="background-color: var(--custom-green);">
                <h4>Kelompok ' . htmlspecialchars($group_number) . '</h4>
            </div>
            <div class="card-body" style="background-color: var(--custom-blue);">
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

// Build indicators HTML for Tahap 1
$indicators_html = '';
if ($step1_status == "Selesai" && !empty($step->analysis_data)) {
    $indicators = json_decode($step->analysis_data, true);
    if (is_array($indicators) && count($indicators) > 0) {
        $indicators_html = '
        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-success">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Indikator Penyebab</th>
                        <th style="width: 60%;">Analisis & Sumber Referensi</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($indicators as $index => $indicator) {
            $indicators_html .= '
                    <tr>
                        <td class="text-center">' . ($index + 1) . '</td>
                        <td>' . htmlspecialchars($indicator['indicator'] ?? '') . '</td>
                        <td>' . nl2br(htmlspecialchars($indicator['analysis'] ?? '')) . '</td>
                    </tr>';
        }
        
        $indicators_html .= '
                </tbody>
            </table>
        </div>';
    } else {
        $indicators_html = '<div class="alert alert-info">Belum ada indikator yang ditambahkan.</div>';
    }
} else {
    $indicators_html = '<div class="alert alert-info">Belum ada indikator yang ditambahkan.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep1">
    <div class="row">
        <div class="col-12">' .
            ($step1_status == "Selesai" ? 
                '<h3>Tahap 1</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Rumusan Masalah & Analisis</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Studi Kasus:</strong> ' . htmlspecialchars($ebelajar_records->case_study) . '</p>
                        <hr style="border-color: var(--custom-green);">
                        
                        <p><strong>Rumusan Masalah:</strong></p>
                        <div class="bg-white p-3 rounded mb-3">' . 
                            (!empty($step->step1_formulation) ? 
                                nl2br(htmlspecialchars($step->step1_formulation)) : 
                                '<span class="badge bg-warning text-dark">Belum ada rumusan masalah</span>'
                            ) . 
                        '</div>
                        
                        <p><strong>Orientasi Masalah:</strong></p>
                        <div class="bg-white p-3 rounded mb-3">' . 
                            (!empty($step->problem_definition) ? 
                                nl2br(htmlspecialchars($step->problem_definition)) : 
                                '<span class="badge bg-warning text-dark">Belum ada orientasi masalah</span>'
                            ) . 
                        '</div>
                        
                        <p><strong>Indikator Penyebab Masalah:</strong></p>
                        ' . $indicators_html . '
                    </div>
                </div>' : 
                '<h3>Tahap 1</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 1.
                </div>'
            ) . 
        '</div>
    </div>
</div>';



// Build schedule HTML for Tahap 2
$schedule_html = '';
if (!empty($step->planning_data)) {
    $schedule_data = json_decode($step->planning_data, true);
    if (is_array($schedule_data) && count($schedule_data) > 0) {
        $schedule_html = '
        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-success">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Kegiatan</th>
                        <th style="width: 15%;">Tanggal Mulai</th>
                        <th style="width: 15%;">Tanggal Selesai</th>
                        <th style="width: 35%;">Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($schedule_data as $index => $task) {
            $schedule_html .= '
                    <tr>
                        <td class="text-center">' . ($index + 1) . '</td>
                        <td>' . htmlspecialchars($task['task'] ?? '') . '</td>
                        <td>' . htmlspecialchars($task['start_date'] ?? '') . '</td>
                        <td>' . htmlspecialchars($task['end_date'] ?? '') . '</td>
                        <td>' . htmlspecialchars($task['pic'] ?? '') . '</td>
                    </tr>';
        }
        
        $schedule_html .= '
                </tbody>
            </table>
        </div>';
    } else {
        $schedule_html = '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
    }
} else {
    $schedule_html = '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep2">
    <div class="row">
        <div class="col-12">' .
            ($step2_status == "Selesai" ? 
                '<h3>Tahap 2 dan Tahap 3</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Perencanaan & Jadwal Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
                    </div>
                </div>' : 
            ($step2_status == "Mengerjakan" ? 
                '<h3>Tahap 2 dan Tahap 3</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Perencanaan & Jadwal Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
                    </div>
                </div>' :
                '<h3>Tahap 2 dan Tahap 3</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 2.
                </div>'
            )) . 
        '</div>
    </div>
</div>';

// Build logbook HTML for Tahap 3
$logbook_html = '';
if (!empty($step->logbook_data)) {
    $logbook_entries = json_decode($step->logbook_data, true);
    if (is_array($logbook_entries) && count($logbook_entries) > 0) {
        $logbook_html = '
        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-success">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 35%;">Kegiatan</th>
                        <th style="width: 25%;">Kendala</th>
                        <th style="width: 10%;">Progres</th>
                        <th style="width: 13%;">Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($logbook_entries as $index => $entry) {
            $progress = $entry['progress'] ?? 0;
            if ($progress >= 100) {
                $status_badge = '<span class="badge bg-success">Selesai</span>';
            } elseif ($progress >= 50) {
                $status_badge = '<span class="badge bg-warning text-dark">Progres</span>';
            } else {
                $status_badge = '<span class="badge bg-secondary">Mulai</span>';
            }
            
            $logbook_html .= '
                    <tr>
                        <td class="text-center">' . ($index + 1) . '</td>
                        <td>' . htmlspecialchars($entry['date'] ?? '') . '</td>
                        <td>' . nl2br(htmlspecialchars($entry['activity'] ?? '')) . '</td>
                        <td>' . nl2br(htmlspecialchars($entry['obstacles'] ?? '-')) . '</td>
                        <td class="text-center"><span class="badge ' . ($progress >= 100 ? 'bg-success' : 'bg-info') . '">' . htmlspecialchars($progress) . '%</span></td>
                        <td>' . $status_badge . '</td>
                    </tr>';
        }
        
        $logbook_html .= '
                </tbody>
            </table>
        </div>';
    } else {
        $logbook_html = '<div class="alert alert-info">Belum ada catatan logbook.</div>';
    }
} else {
    $logbook_html = '<div class="alert alert-info">Belum ada catatan logbook.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep3">
    <div class="row">
        <div class="col-12">' .
            ($step3_status == "Selesai" ? 
                '<h3>Tahap 4</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Logbook Pelaksanaan Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Catatan harian pelaksanaan proyek kelompok:</strong></p>
                        ' . $logbook_html . '
                    </div>
                </div>' : 
            ($step3_status == "Mengerjakan" ? 
                '<h3>Tahap 4</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Logbook Pelaksanaan Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Catatan harian pelaksanaan proyek kelompok:</strong></p>
                        ' . $logbook_html . '
                    </div>
                </div>' :
                '<h3>Tahap 4</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 3.
                </div>'
            )) . 
        '</div>
    </div>
</div>';

/* DISABLED: Old Step 4 Activity Report (Replaced by Logbook System in Step 3)
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
END DISABLED OLD STEP 4 */



echo '
<div class="container mx-auto p-3" id="dataStep5">
    <div class="row">
        <div class="col-12">' .
            ($step5_status == "Selesai" && !empty($step->product_data) ? 
                '<h3>Tahap 5: Pengumpulan Proyek</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Data Proyek Kelompok</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">' .
                        (function() use ($step, $cmid, $DB) {
                            $product_info = json_decode($step->product_data, true);
                            $output = '<p><strong>Deskripsi Proyek:</strong></p>';
                            $output .= '<div class="bg-white p-3 rounded mb-3">' . nl2br(htmlspecialchars($product_info['description'] ?? 'Tidak ada deskripsi.')) . '</div>';
                            
                            if (!empty($product_info['youtube_link'])) {
                                $output .= '<p><strong>Link YouTube:</strong></p>';
                                $output .= '<p><a href="' . htmlspecialchars($product_info['youtube_link']) . '" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fab fa-youtube"></i> Lihat Video</a></p>';
                            }
                            
                            if (!empty($product_info['filename'])) {
                                $fs = get_file_storage();
                                $cm = get_coursemodule_from_id('ebelajar', $cmid, 0, false, MUST_EXIST);
                                $context = context_module::instance($cm->id);
                                $file = $fs->get_file(
                                    $context->id,
                                    'mod_ebelajar',
                                    'product_evidence',
                                    $step->id,
                                    '/',
                                    $product_info['filename']
                                );
                                if ($file && !$file->is_directory()) {
                                    $file_url = moodle_url::make_pluginfile_url(
                                        $context->id,
                                        'mod_ebelajar',
                                        'product_evidence',
                                        $step->id,
                                        '/',
                                        $product_info['filename']
                                    );
                                    $output .= '<p><strong>File Dokumen Proyek:</strong></p>';
                                    $output .= '<p><a href="' . $file_url . '" class="btn btn-success btn-sm" download><i class="fas fa-download"></i> Download: ' . htmlspecialchars($product_info['filename']) . '</a></p>';
                                } else {
                                    $output .= '<p><strong>File Dokumen Proyek:</strong> File tidak ditemukan.</p>';
                                }
                            } else {
                                $output .= '<p><strong>File Dokumen Proyek:</strong> Tidak ada file yang diunggah.</p>';
                            }
                            
                            $output .= '<p class="text-muted small mt-3"><i class="fas fa-clock"></i> Diunggah: ' . htmlspecialchars($product_info['uploaded_at'] ?? '-') . '</p>';
                            return $output;
                        })() .
                    '</div>
                </div>' :
            ($step5_status == "Mengerjakan" ? 
                '<h3>Tahap 5: Pengumpulan Proyek</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum mengumpulkan proyek.
                </div>' :
                '<h3>Tahap 5: Pengumpulan Proyek</h3>
                <div class="alert alert-warning">
                    Kelompok ini belum menyelesaikan tahap 4.
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

