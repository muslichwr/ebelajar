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

echo '
<div class="container mx-auto p-3" id="dataStep1">
    <div class="row">
        <div class="col-12">' .
            ($step1_status == "Selesai" ? 
                '<h3>Tahap 1</h3>
                <div class="card">
                    <div class="card-header text-white" style="background-color: var(--custom-green);">
                        <h4>Rumusan Masalah</h4>
                    </div>
                    <div class="card-body" style="background-color: var(--custom-blue);">
                        <p><strong>Studi Kasus:</strong> ' . $ebelajar_records->case_study . '</p>
                        <p><strong>Rumusan masalah:</strong> ' . 
                            (!empty($step->step1_formulation) ? 
                                $step->step1_formulation : 
                                '<span class="badge rounded-pill bg-warning text-dark">Tambahkan rumusan masalah menurut kelompok anda!</span>'
                            ) . 
                        '</p>
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

