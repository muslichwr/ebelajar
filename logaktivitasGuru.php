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
                '<h3>Tahap 2</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Penyusunan Jadwal Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
                    </div>
                </div>' : 
            ($step2_status == "Mengerjakan" ? 
                '<h3>Tahap 2</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Penyusunan Jadwal Proyek</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
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

