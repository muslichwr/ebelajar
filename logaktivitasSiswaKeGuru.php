<?php

require_once('../../config.php');

redirect_if_major_upgrade_required();

require_login();

function konversiTanggal($tanggal) {
    $tanggal_format = date('d-m-Y', $tanggal);

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

    $tanggal_pisah = explode('-', $tanggal_format);
    $hari = $tanggal_pisah[0];
    $bulan_angka = (int)$tanggal_pisah[1];
    $tahun = $tanggal_pisah[2];

    $tanggal_indonesia = $hari . ' ' . $bulan[$bulan_angka] . ' ' . $tahun;

    return $tanggal_indonesia;
}


global $USER, $DB;
$id_siswa = $_GET['id_siswa'];
$id = $_GET['id'];
$group_project = $_GET['kelompok'];

$user_id = $id_siswa;
$query = "SELECT * FROM {groupstudentproject} WHERE user_id = :user_id";
$params = ['user_id' => $user_id];
$result = $DB->get_record_sql($query, $params);

if ($result) {
    $group_project = $result->groupproject;
    $project_data = $DB->get_record('project', ['group_project' => $group_project]);
} else {
    $project_data = null;
}

$query2 = "SELECT * FROM {activity_report} WHERE user_id = :user_id ORDER BY date_activity ASC";
$params2 = ['user_id' => $user_id];
$results2 = $DB->get_records_sql($query2, $params2);

?>

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

    <nav class="container navbar navbar-expand-md navbar-light px-2 rounded-2" style="background-color: var(--custom-green); border: 4px solid var(--custom-blue);">
        <div class="container-fluid px-md-5">
            <div class="logo mx-auto">
                <!-- <img class="navbar-brand" src="path-to-your-logo.png" alt="Logo" width="100"> -->
                <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pb-3 pt-5">
        <div class="d-flex justify-content-start">
            <button class="btn d-flex align-items-center fw-bold" onclick="kembalimenu2(<?php echo $group_project; ?>, <?php echo $id; ?>)" style="background-color: var(--custom-blue); color: var(--custom-red);">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </button>
        </div>
    </div>

    <div class="container mx-auto p-3">   
        <div id="dataContainer">
            <?php
                if ($result) {
                    echo '
                    <div class="d-flex justify-content-center gap-2">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">' . $result->name_student . '</div>
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">Kelompok ' .$result->groupproject . '</div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">Jobdesk : ' . $result->jobdesk . '</div>
                    </div>';            
                } else {
                    echo '
                    <div class="d-flex justify-content-center gap-2">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum menambahkan data</div>
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum gabung kelompok</div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum ada jobdesk</div>
                    </div>
                    ';
                }
            ?>
        </div>
    </div>

    <div class="container mx-auto p-3" style="overflow-x: auto;">
        <table id="table" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">No</th>
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
                        <td>' . htmlspecialchars($row->name_activity) . '</td>
                        <td>' . konversiTanggal($row->date_activity) . '</td>
                        <td class="text-center">
                            <button class="btn rounded-pill lihat-btn" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalLihatFoto" data-foto-url="' . htmlspecialchars($row->file_path) . '">Lihat</button>
                        </td>';

                    if (!empty($row->feedback_teacher)) {
                        echo '<td class="text-left">'. htmlspecialchars($row->feedback_teacher) . '</td>';
                    } else {
                        echo '
                        <td class="text-center">
                            <span class="badge rounded-pill bg-danger text-white">Tidak ada feedback dari guru</span>
                        </td>';
                    }

                    echo '
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">';
                    if (!empty($row->feedback_teacher)) {
                        echo '<button class="btn btnedit rounded-pill" style="background-color: var(--custom-green); color:#FFFFFF" data-id="' . $row->id . '" data-feedback="' . $row->feedback_teacher . '">Ubah</button>';
                    } else {
                        echo '<button class="btn btn-danger btntambah rounded-pill" data-id="' . $row->id . '">Tambahkan</button>';
                    }
                    echo '        
                        </div>
                    </td>';
                        
                    echo '</tr>';


                    $counter++;
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Tidak ada data yang ditemukan.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="modalLihatFoto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--custom-green); color:#000000">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Foto Bukti Kegiatan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
