<?php

require_once('../../config.php');

redirect_if_major_upgrade_required();

require_login();

function konversiTanggal($tanggal) {
    // Mengubah tanggal dari format Y-m-d ke format d-m-Y
    $tanggal_format = date('d-m-Y', $tanggal);

    // Array bulan dalam bahasa Indonesia
    $bulan = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    );

    // Pisahkan tanggal menjadi hari, bulan, dan tahun
    $tanggal_pisah = explode('-', $tanggal_format);
    $hari = $tanggal_pisah[0];
    $bulan_angka = (int)$tanggal_pisah[1];
    $tahun = $tanggal_pisah[2];

    // Format tanggal dalam bahasa Indonesia
    $tanggal_indonesia = $hari . ' ' . $bulan[$bulan_angka] . ' ' . $tahun;

    return $tanggal_indonesia;
}

global $USER, $DB;

$cmid = $_GET['id'];
$group = $_GET['kelompok'];

$result = intval($group);

$groups_number = $DB->get_field('groupproject', 'group_number', ['id' => $group]);

$ebelajar_records = $DB->get_record('ebelajar', ['coursemoduleid' => $cmid]);
$ebelajar = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
$groupproject_records = $DB->get_records('groupproject', ['ebelajar' => $ebelajar]);
$step3_schedule_image = $DB->get_field('ebelajar', 'step3_schedule_image', ['coursemoduleid' => $cmid]);
$step3_schedule_file = $DB->get_field('ebelajar', 'step3_schedule_file', ['coursemoduleid' => $cmid]);

if ($result) {
    $group_project = $result;
    $project_data = $DB->get_record('project', ['status_step5' => "Selesai", 'group_project' => $group_project]);
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

// Fetch indicators for display
$indicators = [];
if (isset($step) && $step && isset($step->id)) {
    $indicators = $DB->get_records('project_indicators', 
        ['project_id' => $step->id], 
        'created_at ASC'
    );
}

$query2 = "
    SELECT ar.*, u.username, u.firstname, u.lastname
    FROM {activity_report} ar
    JOIN {user} u ON ar.user_id = u.id
    WHERE ar.groupproject = :groupproject
    ORDER BY ar.date_activity ASC
";
$params2 = ['groupproject' => $result];
$results2 = $DB->get_records_sql($query2, $params2);

?>

<style>
    :root {
            --custom-blue: #bed4d1;
            --custom-green: #5a9f68;
            --custom-red: #ff5757;
        }
    .navskuy {
        background-color: var(--custom-green); 
        border: 4px solid var(--custom-blue);
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo {
        display: inline-block;
    }
</style>

    <nav class="container navbar navbar-expand-md navbar-light px-2 rounded-2" style="background-color: var(--custom-green); border: 4px solid var(--custom-blue);">
        <div class="container-fluid px-md-5">
            <div class="logo mx-auto">
                <!-- <img class="navbar-brand" src="path-to-your-logo.png" alt="Logo" width="100"> -->
                <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <div class="col-lg-6 col-12 text-center mb-4 mb-lg-0">
                <img src="assets/guru-awal(3).svg" class="img-fluid" alt="Ilustrasi">
            </div>
            <div class="col-lg-6 col-12 text-center">
                <h1 class="fw-bolder text-white"><strong>KELOMPOK <?php echo htmlspecialchars($groups_number); ?></strong></h1>
            </div>
        </div>
    </div>

    <div class="container mx-auto pb-3 pt-5">
        <div class="d-flex justify-content-start">
            <button class="btn d-flex align-items-center fw-bold" onclick="kembali()" style="background-color: var(--custom-blue); color: var(--custom-red);">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </button>
        </div>
    </div>

    <div class="container mx-auto p-3" id="dataStep1">
        <div class="row">
            <div class="col-12">
                <?php if ($step1_status == "Selesai"): ?>
                    <h3>Tahap 1</h3>
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4><i class="fas fa-lightbulb"></i> Penentuan Pertanyaan Mendasar</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <!-- Scenario Display -->
                            <div class="mb-4">
                                <h5><i class="fas fa-book-open"></i> Skenario:</h5>
                                <div class="p-3 bg-white rounded">
                                    <?php 
                                    $scenario = !empty($ebelajar_records->teacher_scenario) 
                                        ? $ebelajar_records->teacher_scenario 
                                        : $ebelajar_records->case_study;
                                    echo nl2br(htmlspecialchars($scenario)); 
                                    ?>
                                </div>
                            </div>

                            <!-- Problem Formulation -->
                            <div class="mb-4">
                                <h5><i class="fas fa-question-circle"></i> Rumusan Masalah:</h5>
                                <div class="p-3 bg-white rounded">
                                    <?php 
                                    if (!empty($step->step1_formulation)) {
                                        echo nl2br(htmlspecialchars($step->step1_formulation));
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Belum diisi</span>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Indicators Table -->
                            <?php if (!empty($indicators)): ?>
                            <div class="mb-3">
                                <h5><i class="fas fa-list-check"></i> Indikator & Analisis (<?php echo count($indicators); ?>):</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover bg-white">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%" class="text-center">#</th>
                                                <th style="width: 25%">Indikator</th>
                                                <th style="width: 35%">Analisis</th>
                                                <th style="width: 25%">Referensi</th>
                                                <th style="width: 10%" class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $no = 1;
                                        foreach ($indicators as $ind): 
                                            $refs = json_decode($ind->references, true);
                                            $is_invalid = ($ind->is_valid == 0);
                                            $row_class = $is_invalid ? 'table-secondary text-muted' : '';
                                        ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="text-center fw-bold"><?php echo $no++; ?></td>
                                                <td class="<?php echo $is_invalid ? 'text-decoration-line-through' : ''; ?>">
                                                    <?php echo htmlspecialchars($ind->indicator_name); ?>
                                                </td>
                                                <td class="<?php echo $is_invalid ? 'text-decoration-line-through' : ''; ?>">
                                                    <?php echo nl2br(htmlspecialchars($ind->analysis)); ?>
                                                </td>
                                                <td>
                                                    <?php if (is_array($refs) && count($refs) > 0): ?>
                                                        <ol class="mb-0 ps-3 small">
                                                        <?php foreach ($refs as $ref): ?>
                                                            <li class="mb-1">
                                                                <a href="<?php echo htmlspecialchars($ref); ?>" target="_blank" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars(strlen($ref) > 50 ? substr($ref, 0, 50) . '...' : $ref); ?>
                                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                        </ol>
                                                        <?php if (count($refs) < 3): ?>
                                                            <small class="text-danger d-block mt-1">
                                                                <i class="fas fa-exclamation-triangle"></i> Kurang dari 3 referensi
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Tidak ada referensi</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($is_invalid): ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times-circle"></i> Tidak Terbukti
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle"></i> Terbukti
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada indikator yang ditambahkan oleh kelompok.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h3>Tahap 1</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 1.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3" id="dataStep2">
        <div class="row">
            <div class="col-12">
                <?php if ($step2_status == "Selesai"): ?>
                    <h3>Tahap 2</h3>
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4>Penyusunan Indikator</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                            <p>
                                <strong>file:</strong> 
                                <?php 
                                    if (!empty($step->step2_pondation)) {
                                        echo '<a href="'. (new moodle_url('/mod/ebelajar/' . $step->step2_pondation))->out() .'" download>'.basename($step->step2_pondation).'</a>';
                                    } else {
                                        echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompo anda!</span>';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php elseif ($step2_status == "Mengerjakan"): ?>
                    <h3>Tahap 2</h3>
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4>Penyusunan Indikator</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                            <p>
                                <strong>file:</strong> 
                                <?php 
                                    if (!empty($step->step2_pondation)) {
                                        echo $step->step2_pondation;
                                    } else {
                                        echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompo anda!</span>';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php elseif ($step2_status == "Belum Selesai"): ?>
                    <h3>Tahap 2</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 2.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3" id="dataStep3">
        <div class="row">
            <div class="col-12">
                <?php if ($step3_schedule_image && $step3_schedule_file != null && $step3_status == "Selesai"): ?>
                    <h3>Tahap 3</h3>
                    <!-- Jika ada data, tampilkan dalam card -->
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4>Jadwal Proyek Untuk Siswa</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <img src="<?php echo(new moodle_url('/mod/ebelajar/' . $step3_schedule_image))->out(); ?>" alt="gambar jadwal perencanaan" class="d-block mx-auto w-50 h-50">
                            <?php if (!empty($step3_schedule_file)): ?>
                                <p><strong>File:</strong> <a href="<?php echo $step3_schedule_file; ?>" download><?php echo basename($step3_schedule_file); ?></a></p>
                            <?php else: ?>
                                <p><strong>File:</strong> Tidak ada file yang diunggah.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($step3_schedule_image && $step3_schedule_file == null && $step3_status == "Mengerjakan"): ?>
                    <div class="alert alert-warning">
                        Guru belum menambahkan perjadwalan! harap tunggu sebentar atau silahkan menghubungi guru.
                    </div>
                <?php else: ?>
                    <h3>Tahap 3</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 3.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($step4_status == "Belum Selesai"): ?>
        <div class="container mx-auto p-3" id="dataStep4">
            <div class="row">
                <div class="col-12">
                    <h3>Tahap 4</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 4.
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container mx-auto px-3 py-5 p-md-5" id="dataStep4" style="overflow-x: auto;">
            <h3>Tahap 4</h3>
            <table id="table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Siswa</th>
                        <th>Nama Kegiatan</th>
                        <th>Tanggal Kegiatan</th>
                        <th class="text-center">Foto Kegiatan</th>
                        <th class="text-center">Feedback Guru</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($results2) {
                    $counter = 1;
                    foreach ($results2 as $row) {
                        echo '
                        <tr>
                            <td class="text-center">' . $counter . '</td>
                            <td>' . htmlspecialchars($row->firstname) . ' ' . htmlspecialchars($row->lastname) . '</td>
                            <td>' . htmlspecialchars($row->name_activity) . '</td>
                            <td>' . konversiTanggal($row->date_activity) . '</td>
                            <td class="text-center">
                                <button class="btn rounded-pill lihat-btn" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalLihatFoto" data-foto-url="' . htmlspecialchars($row->file_path) . '">Lihat</button>
                            </td>';

                        if (!empty($row->feedback_teacher)) {
                            echo '<td class="text-left">'. htmlspecialchars($row->feedback_teacher) . '</td>';
                            echo '
                                <td class="text-center">
                                    <span class="badge rounded-pill text-white" style="background-color: var(--custom-red); color:#FFFFFF">Tidak dapat melakukan perubahan</span>
                                </td>
                            </tr>';
                        } else {
                            echo '
                            <td class="text-center">
                                <span class="badge rounded-pill bg-danger text-white">Tidak ada feedback dari guru</span>
                            </td>';
                            echo '
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn rounded-pill btnedit" style="background-color: var(--custom-green); color:#FFFFFF" 
                                                data-id="' . $row->id . '"
                                                data-groupproject="' . htmlspecialchars($row->groupproject) . '" 
                                                data-nama="' . htmlspecialchars($row->name_activity) . '" 
                                                data-uraian="' . htmlspecialchars($row->description_activity) . '" 
                                                data-tanggal="' . date('Y-m-d', $row->date_activity) . '"
                                                data-file="'. $row->file_path .'">
                                            Edit
                                        </button>
                                        <button class="btn btn-danger rounded-pill" onclick="hapusKegiatan(' . $row->id . ')">Hapus</button>
                                    </div>
                                </td>
                            ';                    
                        }

                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Tidak ada data yang ditemukan.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


    <div class="container mx-auto p-3" id="dataStep5">
        <div class="row">
            <div class="col-12">
                <?php if ($project_data && $step5_status == "Selesai"): ?>
                    <h3>Tahap 5</h3>
                    <!-- Jika ada data, tampilkan dalam card -->
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4>Data Project Kelompok Anda</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <p><strong>Judul Project:</strong> <?php echo $project_data->title_project; ?></p>
                            <p><strong>Deskripsi Project:</strong> <?php echo $project_data->description_project; ?></p>
                            
                            <?php if (!empty($project_data->file_path)): ?>
                                <p><strong>File:</strong> <a href="<?php echo $project_data->file_path; ?>" download><?php echo basename($project_data->file_path); ?></a></p>
                            <?php else: ?>
                                <p><strong>File:</strong> Tidak ada file yang diunggah.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($step5_status == "Mengerjakan"): ?>
                    <h3>Tahap 5</h3>
                    <div class="alert alert-warning">
                        Kelompok mu belum menambahkan data project. Yuk jikalau sudah selesai silahkan dikumpulkan.
                    </div>
                <?php elseif ($step5_status == "Belum Selesai"): ?>
                    <h3>Tahap 5</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 5.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3" id="dataStep6">
        <div class="row">
            <div class="col-12">
                <?php if ($project_data && $step6_status == "Selesai"): ?>
                    <h3>Tahap 6</h3>
                    <!-- Jika ada data, tampilkan dalam card -->
                    <div class="card">
                        <div class="card-header text-white" style="background-color: var(--custom-green);">
                            <h4>Data Evaluasi Kelompokmu</h4>
                        </div>
                        <div class="card-body" style="background-color: var(--custom-blue);">
                            <p><strong>Evaluasi:</strong> <?php echo $project_data->evaluation; ?></p>
                        </div>
                    </div>
                <?php elseif ($step6_status == "Mengerjakan"): ?>
                    <h3>Tahap 6</h3>
                    <div class="alert alert-warning">
                        Kelompok mu belum menambahkan evaluasi. Yuk jikalau sudah selesai silahkan dikumpulkan.
                    </div>
                <?php elseif ($step6_status == "Belum Selesai"): ?>
                    <h3>Tahap 6</h3>
                    <div class="alert alert-warning">
                        Kelompok ini belum menyelesaikan tahap 6.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
