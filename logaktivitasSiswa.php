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
$user_id = $USER->id;
$cmid = required_param('id', PARAM_INT);
$ebelajar = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
$query = "
    SELECT gs.*, gp.group_number
    FROM {groupstudentproject} gs
    JOIN {groupproject} gp ON gs.groupproject = gp.id
    WHERE gs.user_id = :user_id AND gs.ebelajar = :ebelajar
";
$params = ['user_id' => $user_id, 'ebelajar' => $ebelajar];
$result = $DB->get_record_sql($query, $params);

$ebelajar_records = $DB->get_record('ebelajar', ['coursemoduleid' => $cmid]);
$groupproject_records = $DB->get_records('groupproject', ['ebelajar' => $ebelajar]);
$step3_schedule_image = $DB->get_field('ebelajar', 'step3_schedule_image', ['coursemoduleid' => $cmid]);
$step3_schedule_file = $DB->get_field('ebelajar', 'step3_schedule_file', ['coursemoduleid' => $cmid]);

$project_records = $DB->get_records('project', ['ebelajar' => $ebelajar]);
$filtered_records = [];

foreach ($project_records as $record) {
    if ($result && $record->group_project != $result->groupproject) {
        $groups_project = $DB->get_record('groupproject', ['id' => $record->group_project], 'group_number');
        
        if ($groups_project) {
            $record->group_number = $groups_project->group_number;
        }
        
        $filtered_records[] = $record;
    }
}

if ($result) {
    $group_project = $result->groupproject;
    $project_data = $DB->get_record('project', ['status_step5' => "Selesai", 'group_project' => $group_project]);
    $step = $DB->get_record('project', ['group_project' => $group_project]);
    $step1_status = $DB->get_field('project', 'status_step1', ['group_project' => $group_project]);
    $step2_status = $DB->get_field('project', 'status_step2', ['group_project' => $group_project]);
    $step3_status = $DB->get_field('project', 'status_step3', ['group_project' => $group_project]);
    $step4_status = $DB->get_field('project', 'status_step4', ['group_project' => $group_project]);
    $step5_status = $DB->get_field('project', 'status_step5', ['group_project' => $group_project]);
    $step6_status = $DB->get_field('project', 'status_step6', ['group_project' => $group_project]);
    $step7_status = $DB->get_field('project', 'status_step7', ['group_project' => $group_project]);
    $step8_status = $DB->get_field('project', 'status_step8', ['group_project' => $group_project]);
} else {
    $project_data = null;
    $group_project = null;
    $step1_status = null;
    $step2_status = null;
    $step3_status = null;
    $step4_status = null;
    $step5_status = null;
    $step6_status = null;
    $step7_status = null;
    $step8_status = null;
}

/* DISABLED: Old Step 4 Activity Report Query (No longer used)
$query2 = "
    SELECT ar.*, u.username, u.firstname, u.lastname
    FROM {activity_report} ar
    JOIN {user} u ON ar.user_id = u.id
    WHERE ar.groupproject = :groupproject
    ORDER BY ar.date_activity ASC
";
$params2 = ['groupproject' => $group_project];
$results2 = $DB->get_records_sql($query2, $params2);
*/

?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

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

  <script>
      $(document).ready(function() {
          $('#table').DataTable();
      });
  </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpan').click(function() {
            var formData = $('#formTambahData').serialize();
            $.ajax({
                url: 'formtambahData.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);                  
                    // Tampilkan SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdated').click(function() {
            var formData = $('#formUbahData').serialize();
            $.ajax({
                url: 'formeditData.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahData')[0].reset();
                        $('#modalDataDiriUbahData').modal('hide');
                        $('#dataContainer').load(location.href + ' #dataContainer');
                        $('#dataButtonLoad').load(location.href + ' #dataButtonLoad');
                        $('#editLoad').load(location.href + ' #editLoad');
                        $('#dataProjectContainer').load(location.href + ' #dataProjectContainer');
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanActivity').click(function() {
            var formData = new FormData($('#formTambahDataActivity')[0]);
            var nama_kegiatan = $('[name="nama_kegiatan"]').val();
            var uraian_kegiatan = $('[name="uraian_kegiatan"]').val();
            var tanggal_kegiatan = $('[name="tanggal_kegiatan"]').val();
            var bukti_kegiatan = $('[name="bukti_kegiatan"]').prop('files')[0];

            if (!nama_kegiatan || !uraian_kegiatan || !tanggal_kegiatan) {
                alert("Harap lengkapi semua bidang.");
                return;
            }

            if (!bukti_kegiatan) {
                alert("Harap unggah bukti kegiatan.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataActivity.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataActivity')[0].reset();
                        $('#modalTambahKegiatan').modal('hide');
                        $('#table').load(location.href + ' #table');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var lihatButtons = document.querySelectorAll('.lihat-btn');
        
        lihatButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var imageUrl = this.getAttribute('data-foto-url');
                var modalContent = document.querySelector('#modalLihatFoto .modal-body');
                modalContent.innerHTML = '<img src="' + imageUrl + '" class="img-fluid mx-auto d-block" alt="Foto Kegiatan">';
            });
        });
    });
    </script>
    
    <script>
    function hapusKegiatan(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus saja!'
        }).then((result) => {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "hapus_kegiatan.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Tampilkan pesan berhasil
                        Swal.fire(
                            'Deleted!',
                            'Kegiatan telah berhasil dihapus.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    }
                };
                xhr.send("id=" + id);
            }
        });
    }
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanProject').click(function() {
            var formData = new FormData($('#formTambahDataProject')[0]);
            var title_project = $('[name="title_project"]').val();
            var description_project = $('[name="description_project"]').val();
            var file_project = $('[name="file_project"]').prop('files')[0];

            if (!title_project || !description_project) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataProject.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataProject')[0].reset();
                        $('#modalTambahProject').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdateProject').click(function() {
            var form = $(this).closest('.modal').find('form');

            var formData = new FormData(form[0]);
            
            $.ajax({
                url: 'formeditDataProject.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formEditDataProject')[0].reset();
                        $('#modalEditProject').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        document.querySelectorAll('.btnedit').forEach(button => {
            button.addEventListener('click', function() {
                const rowId = this.getAttribute('data-id');
                const modalId = 'modalEditKegiatan_' + rowId;

                const namaKegiatan = this.getAttribute('data-nama');
                const uraianKegiatan = this.getAttribute('data-uraian');
                const tanggalKegiatan = this.getAttribute('data-tanggal'); 
                const oldFilePath = this.getAttribute('data-file');
                const groupproject = this.getAttribute('data-groupproject');

                let modalHTML = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit Kegiatan</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditDataActivity" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                                        <input type="hidden" name="id" value="${rowId}">
                                        <input type="hidden" name="old_file_path" value="${oldFilePath}">
                                        <input type="hidden" name="group_project" value="${groupproject}>">

                                        <!-- Nama Kegiatan -->
                                        <div class="mb-3">
                                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                                            <input type="text" id="nama_kegiatan" name="nama_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                placeholder="Tambah Kegiatan" 
                                                value="${namaKegiatan}" required>
                                        </div>

                                        <!-- Uraian Kegiatan -->
                                        <div class="mb-3">
                                            <label for="uraian_kegiatan" class="form-label">Uraian Kegiatan</label>
                                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" 
                                                class="form-control shadow-sm rounded-lg p-3" 
                                                style="color:#000000; border: 1px solid #000000;" 
                                                placeholder="Masukkan Uraian Kegiatan" 
                                                rows="4" required>${uraianKegiatan}</textarea>
                                        </div>

                                        <!-- Tanggal Kegiatan -->
                                        <div class="mb-3">
                                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                                            <input type="date" id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                value="${tanggalKegiatan}" required>
                                        </div>

                                        <!-- Bukti Kegiatan -->
                                        <div class="mb-3">
                                            <label for="bukti_kegiatan" class="form-label">Bukti Kegiatan</label>
                                            <input type="file" id="bukti_kegiatan" name="bukti_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                accept=".jpg, .jpeg, .png, .gif">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btnUpdateActivity" type="button" class="btn rounded-pill w-25 btnUpdateActivity" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);

                const modalElement = new bootstrap.Modal(document.getElementById(modalId));
                modalElement.show();

                $('.btnUpdateActivity').click(function() {
                    var form = $(this).closest('.modal').find('form');

                    var formData = new FormData(form[0]);

                    $.ajax({
                        url: 'formeditDataActivity.php',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            console.log(response);               
                            Swal.fire({
                                icon: 'success',
                                title: 'Data berhasil disimpan!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                $(this).closest('.modal').modal('hide');
                                $('#table').load(location.href + ' #table');
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log("Terjadi kesalahan: " + error);
                        }
                    });
                });

                document.getElementById(modalId).addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            });
        });
    });
    </script>


    <script>
    $(document).ready(function() {
        $('#btnSimpanStep2').click(function() {
            var formData = new FormData($('#formTambahDataStep2')[0]);
            var step2_pondation = $('[name="step2_pondation"]').prop('files')[0];

            if (!step2_pondation) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataStep2.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataStep2')[0].reset();
                        $('#modalTambahStep2').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <!-- OLD STEP 6 HANDLER REMOVED: Was checking for 'evaluation' field that no longer exists
         This was causing "Harap lengkapi semua bidang" error when editing presentations.
         New handler is in modalTambahStep6 inline script using window.savePresentationStep6()
    <script>
    $(document).ready(function() {
        $('#btnSimpanStep6').click(function() {
            var formData = new FormData($('#formTambahDataStep6')[0]);
            var evaluation = $('[name="evaluation"]').val();

            if (!evaluation) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            ... OLD CODE REMOVED ...
        });
    });
    </script>
    -->


    <script>
    $(document).ready(function() {
        $('#btnUpdatedStep2').click(function() {
            var formData = new FormData($('#formUbahStep2')[0]);
            $.ajax({
                url: 'formeditDataStep2.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahStep2')[0].reset();
                        $('#modalEditStep2').modal('hide');
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdatedStep6').click(function() {
            var formData = $('#formUbahStep6').serialize();
            $.ajax({
                url: 'formeditDataStep6.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahStep6')[0].reset();
                        $('#modalEditStep6').modal('hide');
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSubmitLihat').click(function() {
            var cmid = $("input[name='cmid']").val();
            console.log("cmid:", cmid);

            const kelompokId = $('#lihat_kelompok').val(); 
            console.log("kelompokId:", kelompokId); 

            if (kelompokId) {
                lihatDetail(cmid, kelompokId);
                $('#formLihatKelompok')[0].reset();
                $('#modalLihatKelompok').modal('hide');
            } else {
                alert("Silakan pilih kelompok terlebih dahulu.");
            }
        });
    });
    </script>

    <script>
    function lihatDetail(id, kelompokId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logaktivitasKelompokSiswa.php?id=" + id + "&kelompok=" + kelompokId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var mainDiv = document.querySelector('div[role="main"]');
                if (mainDiv) {
                    mainDiv.innerHTML = xhr.responseText;
                    $('#table').DataTable();
                }
            }
        };
        xhr.send();
    }

    function kembali() {
        location.reload();
    }
    </script>

    


  <nav class="container navbar navskuy navbar-expand-md navbar-light px-2">
    <div class="container-fluid px-md-5">
        <div class="logo">
            <!-- <img  src="images/logo_elearning.png" alt="Logo e-legbook" style="width: 60px; height: 150px;"> -->
            <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                </li>
            </ul>
            <div class="d-flex flex-column flex-md-row gap-0 gap-md-3">
                <div id="dataButtonLoad">
                <?php
                    if ($result) {
                        echo '<button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-blue); color:#000000" data-bs-toggle="modal" data-bs-target="#modalDataDiriUbahData"><i class="fas fa-plus"></i> Ubah Data Diri</button>';
                    } else {
                        echo '<button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-red); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalDataDiri"><i class="fas fa-plus"></i> Tambah Data Diri</button>';
                    }
                ?>   
                </div>   
            </div>
        </div>
    </div>
  </nav>

  <div class="modal fade" id="modalDataDiri" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Diri</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahData" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">
                    <div class="mb-3">
                        <label for="nama_siswa" class="form-label">Nama Siswa</label>
                        <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Nama Siswa" 
                            class="form-control rounded-2 px-3" 
                            style="color:#000000;" 
                            value="<?php 
                            $firstname = isset($_SESSION["USER"]->firstname) ? $_SESSION["USER"]->firstname : "";
                            $lastname = isset($_SESSION["USER"]->lastname) ? $_SESSION["USER"]->lastname : "";
                            echo htmlspecialchars(trim($firstname) . " " . trim($lastname));  
                            ?>" readonly required>
                    </div>

                    <div class="mb-3 d-flex flex-column">
                        <label for="no_kelompok" class="form-label">Nomor Kelompok</label>
                        <select id="no_kelompok" name="no_kelompok" 
                            class="form-select rounded-2 px-3 py-2 mt-2" 
                            style="color:#000000;" required>
                            <option value="">Pilih No Kelompok</option>
                            <?php
                            foreach ($groupproject_records as $groupproject) {
                                echo '<option value="' . $groupproject->id . '">Kelompok ' . $groupproject->group_number . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jobdesk" class="form-label">Jobdesk</label>
                        <input type="text" id="jobdesk" name="jobdesk" placeholder="Jobdesk" 
                            class="form-control rounded-2 px-3" 
                            style="color:#000000;" required>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpan" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#000000">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>

  <div class="modal fade" id="modalDataDiriUbahData" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Data Diri</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahData" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="id" value="<?php echo $result->id; ?>">

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">Nama Siswa</label>
                            <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Nama Siswa" 
                                class="form-control rounded-2 px-3" 
                                style="color:#000000;" 
                                value="<?php echo $result->name_student; ?>" readonly required>
                        </div>

                        <div class="mb-3 d-flex flex-column">
                            <label for="no_kelompok" class="form-label">Nomor Kelompok</label>
                            <select id="no_kelompok" name="no_kelompok" 
                                class="form-select rounded-2 px-3 py-2 mt-2" 
                                style="color:#000000;" required>
                                <?php
                                foreach ($groupproject_records as $groupproject) {
                                    $selected = ($i == $result->groupproject) ? 'selected' : '';
                                    echo '<option value="' . $groupproject->id . '" ' . $selected . '>Kelompok ' . $groupproject->group_number . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jobdesk" class="form-label">Jobdesk</label>
                            <input type="text" id="jobdesk" name="jobdesk" placeholder="Jobdesk" 
                                class="form-control rounded-2 px-3" 
                                style="color:#000000;" 
                                value="<?php echo $result->jobdesk; ?>" required>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdated" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <h3 class="fw-bolder text-white">Selamat Datang!</h3>
                <p class="text-white">Silahkan melengkapi kegiatan anda selama melakukan aktivitas pembelajaran secara kelompok.</p>
            </div>
            <!-- Kolom gambar -->
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(2).svg" class="img-fluid" alt="Ilustrasi">
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <!-- Kolom gambar -->
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(4).svg" class="img-fluid" alt="Ilustrasi">
            </div>
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <h3 class="fw-bolder text-white">Perlu melihat kelompok lain?</h3>
                <p class="text-white">Silahkan pilih tombol dibawah ini untuk melihat kelompok lain</p>
                <?php if ($result == null): ?>
                    <div class="d-flex justify-content-lg-start justify-content-center mb-2">
                        <button class="btn text-black bg-secondary" disabled><i class="fas fa-xmark"></i> Tambahkan Data Diri dulu</button>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-lg-start justify-content-center mb-2">
                        <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalLihatKelompok"><i class="fas fa-eye"></i> Lihat Kelompok Lain</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3">   
        <div id="dataContainer">
            <?php
                if ($result) {
                    echo '
                    <div class="d-flex justify-content-center gap-2">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">' . $result->name_student . '</div>
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">Kelompok ' .$result->group_number . '</div>
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


    <?php if ($result == null): ?>
        <h1 class="text-center">Silahkan gabung kelompok terlebih dahulu!</h1>
    <?php else: ?>
        <div class="container mx-auto p-3" id="dataStep1">
            <div class="row">
                <div class="col-12">
                    <?php if ($step1_status == "Selesai"): ?>
                        <h3>Tahap 1</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep1"><i class="fas fa-plus"></i> Edit Rumusan masalah</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Rumusan Masalah & Analisis</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Studi Kasus:</strong> <?php echo $ebelajar_records->case_study; ?></p>
                                <p>
                                    <strong>Rumusan masalah:</strong> 
                                    <?php 
                                        if (!empty($step->step1_formulation)) {
                                            echo $step->step1_formulation;
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan rumusan masalah menurut kelompok anda!</span>';
                                        }
                                    ?>
                                </p>
                                
                                <?php if (!empty($step->problem_definition)): ?>
                                <p>
                                    <strong>Orientasi Masalah:</strong> 
                                    <?php echo $step->problem_definition; ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php 
                                // Display saved indicators (Selesai view)
                                if (!empty($step->analysis_data)) {
                                    $indicators_selesai = json_decode($step->analysis_data, true);
                                    if (is_array($indicators_selesai) && count($indicators_selesai) > 0):
                                ?>
                                <div class="mt-3">
                                    <strong>Indikator Penyebab Masalah:</strong>
                                    <div class="mt-2">
                                        <?php foreach ($indicators_selesai as $idx => $ind): ?>
                                        <div class="card mb-2" style="border-left: 4px solid var(--custom-green);">
                                            <div class="card-body py-2 px-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="badge bg-success me-2"><?php echo ($idx + 1); ?></span>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($ind['indicator'] ?? ''); ?></strong>
                                                        <?php if (!empty($ind['analysis'])): ?>
                                                        <p class="mb-0 mt-1 small text-muted">
                                                            <?php echo nl2br(htmlspecialchars($ind['analysis'])); ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php 
                                    endif;
                                }
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3>Tahap 1</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep1"><i class="fas fa-plus"></i> Tambah Rumusan masalah</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Rumusan Masalah</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Studi Kasus:</strong> <?php echo $ebelajar_records->case_study; ?></p>
                                <p>
                                    <strong>Rumusan masalah:</strong> 
                                    <?php 
                                        if (!empty($step->step1_formulation)) {
                                            echo $step->step1_formulation;
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan rumusan masalah menurut kelompok anda!</span>';
                                        }
                                    ?>
                                </p>
                                
                                <?php if (!empty($step->problem_definition)): ?>
                                <p>
                                    <strong>Orientasi Masalah:</strong> 
                                    <?php echo $step->problem_definition; ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php 
                                // Display saved indicators
                                if (!empty($step->analysis_data)) {
                                    $indicators = json_decode($step->analysis_data, true);
                                    if (is_array($indicators) && count($indicators) > 0):
                                ?>
                                <div class="mt-3">
                                    <strong>Indikator Penyebab Masalah:</strong>
                                    <div class="mt-2">
                                        <?php foreach ($indicators as $index => $item): ?>
                                        <div class="card mb-2" style="border-left: 4px solid var(--custom-green);">
                                            <div class="card-body py-2 px-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="badge bg-success me-2"><?php echo ($index + 1); ?></span>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($item['indicator'] ?? ''); ?></strong>
                                                        <?php if (!empty($item['analysis'])): ?>
                                                        <p class="mb-0 mt-1 small text-muted">
                                                            <?php echo nl2br(htmlspecialchars($item['analysis'])); ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php 
                                    endif;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-3" id="dataStep2">
            <div class="row">
                <div class="col-12">
                    <?php if ($step2_status == "Selesai"): ?>
                        <h3>Tahap 2 dan Tahap 3</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep2"><i class="fas fa-edit"></i> Edit Jadwal Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Perencanaan & Jadwal Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Jadwal pelaksanaan proyek kelompok Anda:</strong></p>
                                <?php 
                                if (!empty($step->planning_data)) {
                                    $schedule_data = json_decode($step->planning_data, true);
                                    if (is_array($schedule_data) && count($schedule_data) > 0):
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped bg-white">
                                        <thead class="table-success">
                                            <tr>
                                                <th style="width: 50px;">No</th>
                                                <th>Kegiatan</th>
                                                <th style="width: 130px;">Tanggal Mulai</th>
                                                <th style="width: 130px;">Tanggal Selesai</th>
                                                <th>Penanggung Jawab</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedule_data as $idx => $task): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($task['task'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['start_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['end_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['pic'] ?? ''); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php elseif ($step2_status == "Mengerjakan"): ?>
                        <h3>Tahap 2 dan Tahap 3</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep2"><i class="fas fa-plus"></i> Tambah Jadwal Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Perencanaan & Jadwal Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Silahkan susun jadwal pelaksanaan proyek kelompok Anda.</strong></p>
                                <?php 
                                if (!empty($step->planning_data)) {
                                    $schedule_data = json_decode($step->planning_data, true);
                                    if (is_array($schedule_data) && count($schedule_data) > 0):
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped bg-white">
                                        <thead class="table-success">
                                            <tr>
                                                <th style="width: 50px;">No</th>
                                                <th>Kegiatan</th>
                                                <th style="width: 130px;">Tanggal Mulai</th>
                                                <th style="width: 130px;">Tanggal Selesai</th>
                                                <th>Penanggung Jawab</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedule_data as $idx => $task): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($task['task'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['start_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['end_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['pic'] ?? ''); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-warning">Belum ada jadwal yang disusun. Klik tombol "Tambah Jadwal Proyek" untuk memulai.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-warning">Belum ada jadwal yang disusun. Klik tombol "Tambah Jadwal Proyek" untuk memulai.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php elseif ($step2_status == "Belum Selesai"): ?>
                        <h3>Tahap 2 dan Tahap 3</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 1, selesaikan terlebih dahulu tahap 1.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-3" id="dataStep3">
            <div class="row">
                <div class="col-12">
                    <?php if ($step3_status == "Selesai"): ?>
                        <h3>Tahap 4</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-green);" data-bs-toggle="modal" data-bs-target="#modalTambahStep3"><i class="fas fa-plus"></i> Tambah Catatan Logbook</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Logbook Pelaksanaan Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Catatan harian pelaksanaan proyek kelompok Anda:</strong></p>
                                <?php 
                                if (!empty($step->logbook_data)) {
                                    $logbook_entries = json_decode($step->logbook_data, true);
                                    if (is_array($logbook_entries) && count($logbook_entries) > 0):
                                ?>
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
                                        <tbody>
                                            <?php foreach ($logbook_entries as $idx => $entry): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($entry['date'] ?? ''); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($entry['activity'] ?? '')); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($entry['obstacles'] ?? '-')); ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?php echo ($entry['progress'] ?? 0) >= 100 ? 'bg-success' : 'bg-info'; ?>">
                                                        <?php echo htmlspecialchars($entry['progress'] ?? 0); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $progress = $entry['progress'] ?? 0;
                                                    if ($progress >= 100) {
                                                        echo '<span class="badge bg-success">Selesai</span>';
                                                    } elseif ($progress >= 50) {
                                                        echo '<span class="badge bg-warning text-dark">Progres</span>';
                                                    } else {
                                                        echo '<span class="badge bg-secondary">Mulai</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-info">Belum ada catatan logbook.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-info">Belum ada catatan logbook. Klik tombol "Tambah Catatan Logbook" untuk memulai.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php elseif ($step3_status == "Mengerjakan"): ?>
                        <h3>Tahap 3</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep3"><i class="fas fa-plus"></i> Tambah Catatan Logbook</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Logbook Pelaksanaan Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Silahkan catat progres pelaksanaan proyek kelompok Anda:</strong></p>
                                <?php 
                                if (!empty($step->logbook_data)) {
                                    $logbook_entries = json_decode($step->logbook_data, true);
                                    if (is_array($logbook_entries) && count($logbook_entries) > 0):
                                ?>
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
                                        <tbody>
                                            <?php foreach ($logbook_entries as $idx => $entry): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($entry['date'] ?? ''); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($entry['activity'] ?? '')); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($entry['obstacles'] ?? '-')); ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?php echo ($entry['progress'] ?? 0) >= 100 ? 'bg-success' : 'bg-info'; ?>">
                                                        <?php echo htmlspecialchars($entry['progress'] ?? 0); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $progress = $entry['progress'] ?? 0;
                                                    if ($progress >= 100) {
                                                        echo '<span class="badge bg-success">Selesai</span>';
                                                    } elseif ($progress >= 50) {
                                                        echo '<span class="badge bg-warning text-dark">Progres</span>';
                                                    } else {
                                                        echo '<span class="badge bg-secondary">Mulai</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-warning">Belum ada catatan logbook. Klik tombol "Tambah Catatan Logbook" untuk memulai.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-warning">Belum ada catatan logbook. Klik tombol "Tambah Catatan Logbook" untuk memulai.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php elseif ($step3_status == "Belum Selesai"): ?>
                        <h3>Tahap 3</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 2, selesaikan terlebih dahulu tahap 2.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php /* DISABLED: Old Step 4 Activity Report (Replaced by Logbook System in Step 3)
        <?php if ($step4_status == "Belum Selesai"): ?>
            <div class="container mx-auto p-3" id="dataStep4">
                <div class="row">
                    <div class="col-12">
                        <h3>Tahap 4</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 3, selesaikan terlebih dahulu tahap 3.
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="container mx-auto px-3 py-5 p-md-5" style="overflow-x: auto;">
                <h3>Tahap 4</h3>
                <div class="d-flex justify-content-between px-0 px-md-3 py-2">
                    <div class="mr-auto"></div>
                    <div class="mr-0 px-0 px-md-3">
                        <button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan"><i class="fas fa-plus"></i> Tambah Kegiatan</button>
                    </div>
                </div>
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
        END DISABLED OLD STEP 4 */ ?>



        <!-- STEP 5: PRODUCT TESTING (SYNTAX 5) -->
        <div class="container mx-auto p-3" id="dataStep5">
            <div class="row">
                <div class="col-12">
                    <?php 
                    // Decode product_data JSON from $step
                    $product_info = null;
                    if (!empty($step->product_data)) {
                        $product_info = json_decode($step->product_data, true);
                    }
                    
                    // Check if file exists in Moodle file storage
                    $product_file_url = null;
                    $product_filename = null;
                    if ($product_info && !empty($product_info['filename'])) {
                        $fs = get_file_storage();
                        $context = context_module::instance($cmid);
                        $file = $fs->get_file(
                            $context->id,
                            'mod_ebelajar',
                            'product_evidence',
                            $step->id,
                            '/',
                            $product_info['filename']
                        );
                        if ($file && !$file->is_directory()) {
                            $product_file_url = moodle_url::make_pluginfile_url(
                                $context->id,
                                'mod_ebelajar',
                                'product_evidence',
                                $step->id,
                                '/',
                                $product_info['filename']
                            );
                            $product_filename = $product_info['filename'];
                        }
                    }
                    ?>
                    
                    <?php if ($step5_status == "Selesai" && $product_info): ?>
                        <h3>Tahap 5: Pengumpulan Proyek</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalTambahStep5"><i class="fas fa-edit"></i> Edit Data Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Data Proyek Kelompok</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Deskripsi Proyek:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($product_info['description'] ?? 'Tidak ada deskripsi.')); ?></p>
                                
                                <?php if (!empty($product_info['youtube_link'])): ?>
                                <p><strong>Link YouTube:</strong></p>
                                <p><a href="<?php echo htmlspecialchars($product_info['youtube_link']); ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                                    <i class="fab fa-youtube"></i> Lihat Video
                                </a></p>
                                <?php endif; ?>
                                
                                <?php if ($product_file_url): ?>
                                <p><strong>File Dokumen Proyek:</strong></p>
                                <p>
                                    <a href="<?php echo $product_file_url; ?>" class="btn btn-success btn-sm" download>
                                        <i class="fas fa-download"></i> Download: <?php echo htmlspecialchars($product_filename); ?>
                                    </a>
                                </p>
                                <?php else: ?>
                                <p><strong>File Dokumen Proyek:</strong> Tidak ada file yang diunggah.</p>
                                <?php endif; ?>
                                
                                <p class="text-muted small mt-3">
                                    <i class="fas fa-clock"></i> Diunggah: <?php echo htmlspecialchars($product_info['uploaded_at'] ?? '-'); ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($step5_status == "Mengerjakan"): ?>
                        <h3>Tahap 5: Pengumpulan Proyek</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep5"><i class="fas fa-plus"></i> Kumpulkan Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Pengumpulan Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> Silahkan kumpulkan dokumen proyek kelompok Anda (laporan/presentasi/ZIP) beserta deskripsi dan link video (jika ada).
                                </div>
                            </div>
                        </div>
                    <?php elseif ($step5_status == "Belum Selesai"): ?>
                        <h3>Tahap 5: Pengumpulan Proyek</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 4, selesaikan terlebih dahulu tahap 4.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- STEP 6: PRESENTASI PROYEK (SYNTAX 6) -->
        <div class="container mx-auto p-3" id="dataStep6">
            <div class="row">
                <div class="col-12">
                    <?php
                    // Decode presentation_data JSON
                    $presentation_info = [];
                    if (!empty($step->presentation_data)) {
                        $presentation_info = json_decode($step->presentation_data, true);
                    }
                    
                    // Get file URL if exists
                    $presentation_file_url = null;
                    $presentation_filename = '';
                    if (!empty($presentation_info['filename'])) {
                        $fs = get_file_storage();
                        $file = $fs->get_file(
                            $context->id,
                            'mod_ebelajar',
                            'presentation_file',
                            $step->id,
                            '/',
                            $presentation_info['filename']
                        );
                        if ($file && !$file->is_directory()) {
                            $presentation_file_url = moodle_url::make_pluginfile_url(
                                $context->id,
                                'mod_ebelajar',
                                'presentation_file',
                                $step->id,
                                '/',
                                $presentation_info['filename']
                            );
                            $presentation_filename = $presentation_info['filename'];
                        }
                    }
                    ?>
                    
                    <?php if ($step6_status == "Selesai" && $presentation_info): ?>
                        <h3>Tahap 6: Presentasi Proyek</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalTambahStep6"><i class="fas fa-edit"></i> Edit Presentasi</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Data Presentasi Kelompok</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <?php if (!empty($presentation_info['link_presentation'])): ?>
                                <p><strong>Link Presentasi (Canva/Google Slides):</strong></p>
                                <p>
                                    <a href="<?php echo htmlspecialchars($presentation_info['link_presentation']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Buka Presentasi
                                    </a>
                                </p>
                                <?php endif; ?>
                                
                                <?php if ($presentation_file_url): ?>
                                <p><strong>File Presentasi:</strong></p>
                                <p>
                                    <a href="<?php echo $presentation_file_url; ?>" class="btn btn-success btn-sm" download>
                                        <i class="fas fa-download"></i> Download: <?php echo htmlspecialchars($presentation_filename); ?>
                                    </a>
                                </p>
                                <?php elseif (!empty($presentation_info['filename'])): ?>
                                <p><strong>File Presentasi:</strong> File tidak ditemukan.</p>
                                <?php else: ?>
                                <p><strong>File Presentasi:</strong> Tidak ada file yang diunggah.</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($presentation_info['notes'])): ?>
                                <p><strong>Catatan Tambahan:</strong></p>
                                <div class="bg-white p-3 rounded mb-3"><?php echo nl2br(htmlspecialchars($presentation_info['notes'])); ?></div>
                                <?php endif; ?>
                                
                                <p class="text-muted small mt-3">
                                    <i class="fas fa-clock"></i> Diunggah: <?php echo htmlspecialchars($presentation_info['uploaded_at'] ?? '-'); ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($step6_status == "Mengerjakan"): ?>
                        <h3>Tahap 6: Presentasi Proyek</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep6"><i class="fas fa-plus"></i> Kumpulkan Presentasi</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Presentasi Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> Silahkan kumpulkan materi presentasi kelompok Anda (PPT/PDF) atau link presentasi online (Canva/Google Slides).
                                </div>
                            </div>
                        </div>
                    <?php elseif ($step6_status == "Belum Selesai"): ?>
                        <h3>Tahap 6: Presentasi Proyek</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 5, selesaikan terlebih dahulu tahap 5.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

        <!-- STEP 7: PENILAIAN & EVALUASI (SYNTAX 7) -->
        <div class="container mx-auto p-3" id="dataStep7">
            <div class="row">
                <div class="col-12">
                    <?php
                    // Decode evaluation_data
                    $evaluation_info = [];
                    if (!empty($step->evaluation_data)) {
                        $evaluation_info = json_decode($step->evaluation_data, true);
                    }
                    
                    // Get revision file URL if exists
                    $revision_file_url = null;
                    $revision_filename = '';
                    if (!empty($evaluation_info['revision_file'])) {
                        $fs = get_file_storage();
                        $file = $fs->get_file(
                            $context->id,
                            'mod_ebelajar',
                            'revision_file',
                            $step->id,
                            '/',
                            $evaluation_info['revision_file']
                        );
                        if ($file && !$file->is_directory()) {
                            $revision_file_url = moodle_url::make_pluginfile_url(
                                $context->id,
                                'mod_ebelajar',
                                'revision_file',
                                $step->id,
                                '/',
                                $evaluation_info['revision_file']
                            );
                            $revision_filename = $evaluation_info['revision_file'];
                        }
                    }
                    ?>
                    
                    <?php if ($step6_status == "Selesai" || $step7_status != "Belum Selesai"): ?>
                        <h3>Tahap 7: Penilaian & Evaluasi</h3>
                        
                        <!-- Section A: Feedback Guru -->
                        <div class="card mb-3">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Evaluasi Guru</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <?php if (!empty($evaluation_info['teacher_feedback'])): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-comment-dots"></i> Feedback Guru:</h5>
                                        <p><?php echo nl2br(htmlspecialchars($evaluation_info['teacher_feedback'])); ?></p>
                                    </div>
                                    
                                    <!-- Section B: Revisi Siswa (Only if feedback exists) -->
                                    <div class="mt-4">
                                        <h5>Revisi Proyek</h5>
                                        <p class="text-muted small">Jika ada perbaikan yang perlu dilakukan, silahkan upload file revisi di sini.</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <?php if ($revision_file_url): ?>
                                                    <a href="<?php echo $revision_file_url; ?>" class="btn btn-outline-primary" download>
                                                        <i class="fas fa-download"></i> Download Revisi: <?php echo htmlspecialchars($revision_filename); ?>
                                                    </a>
                                                    <div class="small text-muted mt-1">
                                                        Catatan Revisi: <?php echo htmlspecialchars($evaluation_info['revision_notes'] ?? '-'); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-times-circle"></i> Belum ada revisi yang diunggah.</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalTambahStep7">
                                                <i class="fas fa-upload"></i> Upload Revisi
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock"></i> Belum ada evaluasi dari Guru.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <!-- STEP 8: REFLEKSI PEMBELAJARAN (SYNTAX 8) -->
    <div class="container mx-auto p-3" id="dataStep8">
        <div class="row">
            <div class="col-12">
                <?php
                // Decode reflection_data
                $reflection_info = [];
                if (!empty($step->reflection_data)) {
                    $reflection_info = json_decode($step->reflection_data, true);
                }
                ?>
                
                <?php if ($step7_status == "Selesai" || $step7_status == "Mengerjakan"): ?>
                    <h3>Tahap 8: Refleksi Pembelajaran</h3>
                    
                    <?php if ($step8_status == "Selesai" && !empty($reflection_info)): ?>
                        <!-- Display Reflection Answers -->
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4><i class="fas fa-lightbulb"></i> Refleksi Kelompok</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <div class="card mb-3">
                                    <div class="card-body bg-white">
                                        <h6 class="card-subtitle mb-2 text-muted">Pertanyaan 1</h6>
                                        <p class="fw-bold">Apa pengalaman baru yang kalian dapatkan?</p>
                                        <p class="ms-3"><?php echo nl2br(htmlspecialchars($reflection_info['q1'] ?? '-')); ?></p>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-body bg-white">
                                        <h6 class="card-subtitle mb-2 text-muted">Pertanyaan 2</h6>
                                        <p class="fw-bold">Apa kendala yang dihadapi dan solusinya?</p>
                                        <p class="ms-3"><?php echo nl2br(htmlspecialchars($reflection_info['q2'] ?? '-')); ?></p>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-body bg-white">
                                        <h6 class="card-subtitle mb-2 text-muted">Pertanyaan 3</h6>
                                        <p class="fw-bold">Bagaimana kesan pembelajaran berbasis proyek ini?</p>
                                        <p class="ms-3"><?php echo nl2br(htmlspecialchars($reflection_info['q3'] ?? '-')); ?></p>
                                    </div>
                                </div>
                                
                                <p class="text-muted small mt-3">
                                    <i class="fas fa-clock"></i> Dikirim: <?php echo htmlspecialchars($reflection_info['submitted_at'] ?? '-'); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Show Form Button -->
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep8">
                                <i class="fas fa-pen"></i> Isi Refleksi
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4><i class="fas fa-lightbulb"></i> Refleksi Pembelajaran</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> Silahkan isi refleksi pembelajaran kelompok untuk menyelesaikan proyek PjBL.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h3>Tahap 8: Refleksi Pembelajaran</h3>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Selesaikan tahap 7 (Penilaian & Evaluasi) terlebih dahulu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahProject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Project Kelompok</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Nama Proyek -->
                    <div class="mb-3">
                        <label for="title_project" class="form-label">Nama Proyek</label>
                        <input type="text" id="title_project" name="title_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;" 
                            placeholder="Tambah Judul Proyek" required>
                    </div>

                    <!-- Deskripsi Proyek -->
                    <div class="mb-3">
                        <label for="description_project" class="form-label">Deskripsi Proyek</label>
                        <textarea id="description_project" name="description_project" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Deskripsi Proyek" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>

                    <!-- File Proyek -->
                    <div class="mb-3">
                        <label for="file_project" class="form-label">File Proyek</label>
                        <input type="file" id="file_project" name="file_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanProject" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#000000">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalLihatFoto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--custom-green); color:#000000">
                    <h5 class="modal-title" id="exampleModalLabel">Logo E Learning</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>


    <!-- MODAL TAMBAH STEP 5 - PRODUCT TESTING (INLINE JS + FILE UPLOAD) -->
    <div class="modal fade" id="modalTambahStep5" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep5Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep5Label">Pengumpulan Proyek</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahStep5" method="POST" enctype="multipart/form-data" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Deskripsi Produk -->
                    <div class="mb-3">
                        <label for="product_description" class="form-label fw-bold">Deskripsi Proyek <span class="text-danger">*</span></label>
                        <textarea id="product_description" name="product_description" 
                            class="form-control" 
                            placeholder="Jelaskan proyek yang telah diselesaikan oleh kelompok Anda..." 
                            rows="4" required><?php echo htmlspecialchars($product_info['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Link YouTube (Optional) -->
                    <div class="mb-3">
                        <label for="youtube_link" class="form-label fw-bold">Link Video YouTube (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fab fa-youtube text-danger"></i></span>
                            <input type="url" id="youtube_link" name="youtube_link" 
                                class="form-control" 
                                placeholder="https://www.youtube.com/watch?v=..."
                                value="<?php echo htmlspecialchars($product_info['youtube_link'] ?? ''); ?>">
                        </div>
                        <small class="text-muted">Masukkan link video YouTube jika ada dokumentasi video proyek.</small>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="product_file" class="form-label fw-bold">File Dokumen Proyek (Laporan/Presentasi)</label>
                        <input type="file" id="product_file" name="product_file" 
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
                        <small class="text-muted">Format: JPG, PNG, PDF, DOC, PPT, ZIP (Max: 10MB)</small>
                        <?php if (!empty($product_info['filename'])): ?>
                        <div class="mt-2 alert alert-info py-2">
                            <i class="fas fa-file"></i> File saat ini: <strong><?php echo htmlspecialchars($product_info['filename']); ?></strong>
                            <br><small>Upload file baru akan menggantikan file lama.</small>
                        </div>
                        <?php endif; ?>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep5" type="button" class="btn rounded-pill px-4" style="background-color: var(--custom-green); color:#ffffff">
                      <i class="fas fa-save"></i> Simpan
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    /**
     * Syntax 5 (Project Submission) - Inline JavaScript
     * Uses FormData for file upload via AJAX
     * Following "Zero Latency" Frontend Policy
     */
    (function() {
        'use strict';

        // Global function for saving project submission data with file upload
        window.saveProductStep5 = function(e) {
            if (e) e.preventDefault();

            var form = document.getElementById('formTambahStep5');
            var description = document.getElementById('product_description').value.trim();

            // Validation: Description is required
            if (!description) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Deskripsi Wajib Diisi',
                    text: 'Silahkan masukkan deskripsi proyek.'
                });
                return;
            }

            // Use FormData for file upload (DO NOT use serialize())
            var formData = new FormData(form);

            // Show loading state
            var btnSave = document.getElementById('btnSimpanStep5');
            var originalText = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // AJAX request with FormData
            fetch('formtambahDataStep5.php', {
                method: 'POST',
                body: formData
                // Note: Do NOT set Content-Type header - browser will set it with boundary
            })
            .then(function(response) {
                return response.text();
            })
            .then(function(data) {
                console.log('Step 5 Response:', data);
                
                // Reset button state
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;

                if (data.indexOf('Error') === -1 && data.indexOf('error') === -1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Proyek Berhasil Disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        // Close modal and reload page
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep5'));
                        if (modal) modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: data
                    });
                }
            })
            .catch(function(error) {
                console.error('Step 5 Error:', error);
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal mengirim data. Silahkan coba lagi.'
                });
            });
        };

        // Bind event listener when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            var btnSimpan = document.getElementById('btnSimpanStep5');
            if (btnSimpan) {
                btnSimpan.addEventListener('click', window.saveProductStep5);
            }
        });

    })();
    </script>


    <!-- MODAL TAMBAH STEP 6 - PRESENTASI PROYEK (INLINE JS + FILE UPLOAD) -->
    <div class="modal fade" id="modalTambahStep6" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep6Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep6Label">Presentasi Proyek</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahStep6" method="POST" enctype="multipart/form-data" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Link Presentasi (Optional) -->
                    <div class="mb-3">
                        <label for="link_presentation" class="form-label fw-bold">Link Presentasi Online (Canva/Google Slides/Prezi)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-link text-primary"></i></span>
                            <input type="url" id="link_presentation" name="link_presentation" 
                                class="form-control" 
                                placeholder="https://www.canva.com/design/...">
                        </div>
                        <small class="text-muted">Masukkan link presentasi online jika ada (Canva, Google Slides, Prezi, dll).</small>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="presentation_file" class="form-label fw-bold">File Presentasi (PPT/PDF)</label>
                        <input type="file" id="presentation_file" name="presentation_file" 
                            class="form-control"
                            accept=".ppt,.pptx,.pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        <small class="text-muted">Format: PPT, PPTX, PDF, DOC, JPG, PNG, ZIP (Max: 10MB)</small>
                        <?php if (!empty($presentation_info['filename'])): ?>
                        <div class="mt-2 alert alert-info py-2">
                            <i class="fas fa-file"></i> File saat ini: <strong><?php echo htmlspecialchars($presentation_info['filename']); ?></strong>
                            <br><small>Upload file baru akan menggantikan file lama.</small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                        <textarea id="notes" name="notes" 
                            class="form-control" 
                            placeholder="Tambahkan catatan untuk presentasi jika diperlukan..." 
                            rows="3"><?php echo htmlspecialchars($presentation_info['notes'] ?? ''); ?></textarea>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep6" type="button" class="btn rounded-pill px-4" style="background-color: var(--custom-green); color:#ffffff">
                      <i class="fas fa-save"></i> Simpan
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    /**
     * Syntax 6 (Presentation Submission) - Inline JavaScript
     * Uses FormData for file upload via AJAX
     * Following "Zero Latency" Frontend Policy
     */
    (function() {
        'use strict';

        // Global function for saving presentation data with file upload
        window.savePresentationStep6 = function(e) {
            if (e) e.preventDefault();

        var form = document.getElementById('formTambahStep6');
            var linkPresentation = document.getElementById('link_presentation').value.trim();
            var fileInput = document.getElementById('presentation_file');

            // Check if we're in edit mode (existing file indicator is shown)
            var existingFileAlert = document.querySelector('#modalTambahStep6 .alert-info');
            var isEditMode = existingFileAlert !== null;

            // Validation: For new submission, require link OR file. For edit mode, allow any update.
            if (!isEditMode && !linkPresentation && (!fileInput.files || fileInput.files.length === 0)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Silahkan masukkan link presentasi atau upload file presentasi.'
                });
                return;
            }

            // Use FormData for file upload (DO NOT use serialize())
            var formData = new FormData(form);

            // Show loading state
            var btnSave = document.getElementById('btnSimpanStep6');
            var originalText = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // AJAX request with FormData
            fetch('formtambahDataStep6.php', {
                method: 'POST',
                body: formData
                // Note: Do NOT set Content-Type header - browser will set it with boundary
            })
            .then(function(response) {
                return response.text();
            })
            .then(function(data) {
                console.log('Step 6 Response:', data);
                
                // Reset button state
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;

                if (data.indexOf('Error') === -1 && data.indexOf('error') === -1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Presentasi Berhasil Disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        // Close modal and reload page
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep6'));
                        if (modal) modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: data
                    });
                }
            })
            .catch(function(error) {
                console.error('Step 6 Error:', error);
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal mengirim data. Silahkan coba lagi.'
                });
            });
        };

        // Bind event listener when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            var btnSimpan = document.getElementById('btnSimpanStep6');
            if (btnSimpan) {
                btnSimpan.addEventListener('click', window.savePresentationStep6);
            }
        });

    })();
    </script>

    <!-- MODAL TAMBAH STEP 7 - REVISI PROYEK -->
    <div class="modal fade" id="modalTambahStep7" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep7Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep7Label">Upload Revisi Proyek</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahStep7" method="POST" enctype="multipart/form-data" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Catatan Revisi -->
                    <div class="mb-3">
                        <label for="revision_notes" class="form-label fw-bold">Catatan Perbaikan</label>
                        <textarea id="revision_notes" name="revision_notes" 
                            class="form-control" 
                            placeholder="Jelaskan perbaikan yang telah dilakukan..." 
                            rows="4" required><?php echo htmlspecialchars($evaluation_info['revision_notes'] ?? ''); ?></textarea>
                    </div>

                    <!-- File Revisi -->
                    <div class="mb-3">
                        <label for="revision_file" class="form-label fw-bold">File Revisi</label>
                        <input type="file" id="revision_file" name="revision_file" 
                            class="form-control"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar"
                            <?php echo empty($evaluation_info['revision_file']) ? 'required' : ''; ?>>
                        <small class="text-muted">Format: PDF, DOC, PPT, ZIP (Max: 10MB)</small>
                        
                        <?php if (!empty($evaluation_info['revision_file'])): ?>
                        <div class="mt-2 alert alert-info py-2">
                            <i class="fas fa-file"></i> File revisi saat ini: <strong><?php echo htmlspecialchars($evaluation_info['revision_file']); ?></strong>
                            <br><small>Upload baru akan menggantikan yang lama.</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep7" type="button" class="btn rounded-pill px-4" style="background-color: var(--custom-green); color:#ffffff">
                      <i class="fas fa-save"></i> Kirim Revisi
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        window.saveRevisiStep7 = function(e) {
            if (e) e.preventDefault();
            
            var form = document.getElementById('formTambahStep7');
            var notes = document.getElementById('revision_notes').value.trim();
            
            if (!notes) {
                Swal.fire({icon: 'warning', title: 'Catatan Kosong', text: 'Harap isi catatan perbaikan.'});
                return;
            }

            var formData = new FormData(form);
            var btnSave = document.getElementById('btnSimpanStep7');
            var originalText = btnSave.innerHTML;
            
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            
            fetch('formtambahDataStep7.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(data) {
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                
                if (data.indexOf('Success') !== -1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Revisi Berhasil Dikirim!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep7'));
                        if (modal) modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({icon: 'error', title: 'Gagal', text: data});
                }
            })
            .catch(function(error) {
                console.error(error);
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                Swal.fire({icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.'});
            });
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('btnSimpanStep7');
            if (btn) btn.addEventListener('click', window.saveRevisiStep7);
        });
    })();
    </script>

    <!-- MODAL TAMBAH STEP 8 - REFLEKSI PEMBELAJARAN -->
    <div class="modal fade" id="modalTambahStep8" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep8Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep8Label"><i class="fas fa-lightbulb"></i> Refleksi Pembelajaran</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahStep8" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                    
                    <!-- Hidden field for JSON -->
                    <textarea name="reflection_data" id="hidden_reflection_data" style="display:none;"></textarea>

                    <p class="text-muted mb-4">Silahkan jawab pertanyaan refleksi berikut untuk menyelesaikan proyek PjBL.</p>

                    <!-- Question 1 -->
                    <div class="mb-4">
                        <label for="reflection_q1" class="form-label fw-bold">
                            <span class="badge bg-primary me-2">1</span>
                            Apa pengalaman baru yang kalian dapatkan?
                        </label>
                        <textarea id="reflection_q1" class="form-control" 
                            placeholder="Ceritakan pengalaman baru, pengetahuan, atau keterampilan yang kalian peroleh selama mengerjakan proyek ini..." 
                            rows="4" required></textarea>
                    </div>

                    <!-- Question 2 -->
                    <div class="mb-4">
                        <label for="reflection_q2" class="form-label fw-bold">
                            <span class="badge bg-primary me-2">2</span>
                            Apa kendala yang dihadapi dan solusinya?
                        </label>
                        <textarea id="reflection_q2" class="form-control" 
                            placeholder="Jelaskan kendala atau hambatan yang dihadapi selama proyek berlangsung dan bagaimana kalian mengatasinya..." 
                            rows="4" required></textarea>
                    </div>

                    <!-- Question 3 -->
                    <div class="mb-4">
                        <label for="reflection_q3" class="form-label fw-bold">
                            <span class="badge bg-primary me-2">3</span>
                            Bagaimana kesan pembelajaran berbasis proyek ini?
                        </label>
                        <textarea id="reflection_q3" class="form-control" 
                            placeholder="Bagikan kesan kalian tentang pembelajaran berbasis proyek ini. Apa yang kalian rasakan? Apakah pembelajaran ini bermanfaat?..." 
                            rows="4" required></textarea>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep8" type="button" class="btn rounded-pill px-4" style="background-color: var(--custom-green); color:#ffffff">
                      <i class="fas fa-paper-plane"></i> Kirim Refleksi
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        window.saveRefleksiStep8 = function(e) {
            if (e) e.preventDefault();
            
            var q1 = document.getElementById('reflection_q1').value.trim();
            var q2 = document.getElementById('reflection_q2').value.trim();
            var q3 = document.getElementById('reflection_q3').value.trim();
            
            // Validation
            if (!q1) {
                Swal.fire({icon: 'warning', title: 'Jawaban Kosong', text: 'Harap isi pertanyaan 1.'});
                return;
            }
            if (!q2) {
                Swal.fire({icon: 'warning', title: 'Jawaban Kosong', text: 'Harap isi pertanyaan 2.'});
                return;
            }
            if (!q3) {
                Swal.fire({icon: 'warning', title: 'Jawaban Kosong', text: 'Harap isi pertanyaan 3.'});
                return;
            }

            // Serialize to JSON and put in hidden field
            var reflectionData = JSON.stringify({
                q1: q1,
                q2: q2,
                q3: q3
            });
            document.getElementById('hidden_reflection_data').value = reflectionData;

            var form = document.getElementById('formTambahStep8');
            var formData = new FormData(form);
            var btnSave = document.getElementById('btnSimpanStep8');
            var originalText = btnSave.innerHTML;
            
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            
            fetch('formtambahDataStep8.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(data) {
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                
                if (data.indexOf('Success') !== -1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Refleksi Berhasil Dikirim!',
                        text: 'Selamat! Kamu telah menyelesaikan semua tahapan PjBL.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep8'));
                        if (modal) modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({icon: 'error', title: 'Gagal', text: data});
                }
            })
            .catch(function(error) {
                console.error(error);
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
                Swal.fire({icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.'});
            });
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('btnSimpanStep8');
            if (btn) btn.addEventListener('click', window.saveRefleksiStep8);
        });
    })();
    </script>


    <!-- MODAL TAMBAH STEP 2 - JADWAL PROYEK (INLINE JS) -->
    <div class="modal fade" id="modalTambahStep2" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep2Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep2Label">Tambah Jadwal Proyek</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep2" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Hidden field for final JSON -->
                    <textarea name="planning_data" id="planning_data_add" style="display:none;"></textarea>

                    <!-- Schedule Rows Container -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jadwal Kegiatan Proyek</label>
                        <p class="text-muted small mb-2">Tambahkan kegiatan proyek beserta jadwal dan penanggung jawab.</p>
                        
                        <!-- Container for schedule rows -->
                        <div id="schedule-container-add" class="mb-3">
                            <!-- Initial row will be added by JavaScript -->
                        </div>
                        
                        <!-- Add button -->
                        <button type="button" id="btn-add-schedule-add" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Kegiatan
                        </button>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep2" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getScheduleRowTemplateAdd(index) {
            return '<div class="schedule-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #17a2b8 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-info row-number">Kegiatan #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-schedule">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Nama Kegiatan</label>' +
                    '<input type="text" class="form-control schedule-task" placeholder="Contoh: Riset Awal" required>' +
                '</div>' +
                '<div class="row mb-2">' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Mulai</label>' +
                        '<input type="date" class="form-control schedule-start">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Selesai</label>' +
                        '<input type="date" class="form-control schedule-end">' +
                    '</div>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Penanggung Jawab (PIC)</label>' +
                    '<input type="text" class="form-control schedule-pic" placeholder="Nama anggota yang bertanggung jawab">' +
                '</div>' +
            '</div>';
        }

        function updateScheduleRowNumbersAdd() {
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Kegiatan #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-schedule');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addScheduleRowAdd = function() {
            var container = document.getElementById('schedule-container-add');
            var currentRows = container.querySelectorAll('.schedule-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateAdd(newIndex));
            updateScheduleRowNumbersAdd();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-schedule');
            deleteBtn.addEventListener('click', function() {
                removeScheduleRowAdd(this);
            });
        };

        function removeScheduleRowAdd(btn) {
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.schedule-row');
                row.remove();
                updateScheduleRowNumbersAdd();
            }
        }

        function serializeScheduleAdd() {
            var schedule = [];
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            
            rows.forEach(function(row) {
                var task = row.querySelector('.schedule-task').value;
                var startDate = row.querySelector('.schedule-start').value;
                var endDate = row.querySelector('.schedule-end').value;
                var pic = row.querySelector('.schedule-pic').value;
                
                if (task && task.trim()) {
                    schedule.push({
                        task: task.trim(),
                        start_date: startDate || '',
                        end_date: endDate || '',
                        pic: pic ? pic.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(schedule);
        }

        var modalElementAdd = document.getElementById('modalTambahStep2');
        modalElementAdd.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('schedule-container-add');
            container.innerHTML = '';
            addScheduleRowAdd();
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btn-add-schedule-add').addEventListener('click', addScheduleRowAdd);
            
            document.getElementById('btnSimpanStep2').addEventListener('click', function() {
                var container = document.getElementById('schedule-container-add');
                var rows = container.querySelectorAll('.schedule-row');
                var hasValidRow = false;
                
                rows.forEach(function(row) {
                    var task = row.querySelector('.schedule-task').value;
                    if (task && task.trim()) {
                        hasValidRow = true;
                    }
                });
                
                if (!hasValidRow) {
                    alert('Harap tambahkan minimal satu kegiatan dengan nama kegiatan.');
                    return;
                }
                
                var jsonPayload = serializeScheduleAdd();
                document.getElementById('planning_data_add').value = jsonPayload;
                
                var formData = new FormData(document.getElementById('formTambahDataStep2'));
                
                fetch('formtambahDataStep2.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Jadwal berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        document.getElementById('formTambahDataStep2').reset();
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep2'));
                        modal.hide();
                        location.reload();
                    });
                })
                .catch(function(error) {
                    alert('Terjadi kesalahan saat menyimpan jadwal.');
                });
            });
        });
    })();
    </script>

    <div class="modal fade" id="modalEditProject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Project Kelompok</h5>
              </div>
              <div class="modal-body">
                <form id="formEditDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $project_data->id; ?>">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Nama Proyek -->
                    <div class="mb-3">
                        <label for="title_project" class="form-label">Nama Proyek</label>
                        <input type="text" id="title_project" name="title_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;" 
                            placeholder="Tambah Judul Proyek" 
                            value="<?php echo $project_data->title_project; ?>" required>
                    </div>

                    <!-- Deskripsi Proyek -->
                    <div class="mb-3">
                        <label for="description_project" class="form-label">Deskripsi Proyek</label>
                        <textarea id="description_project" name="description_project" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Deskripsi Proyek" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required><?php echo htmlspecialchars($project_data->description_project); ?>
                        </textarea>

                    </div>

                    <!-- File Proyek -->
                    <div class="mb-3">
                        <label for="file_project" class="form-label">File Proyek</label>
                        <input type="file" id="file_project" name="file_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnUpdateProject" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Update</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalTambahKegiatan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                    <h5 class="modal-title" id="exampleModalLabel">Kegiatan</h5>
                </div>
                <div class="modal-body">
                    <form id="formTambahDataActivity" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">

                        <!-- Nama Kegiatan -->
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                            <input type="text" id="nama_kegiatan" name="nama_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" 
                                placeholder="Tambah Kegiatan" required>
                        </div>

                        <!-- Uraian Kegiatan -->
                        <div class="mb-3">
                            <label for="uraian_kegiatan" class="form-label">Uraian Kegiatan</label>
                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                style="color:#000000; border: 1px solid #000000;" 
                                placeholder="Masukkan Uraian Kegiatan" rows="4" required></textarea>
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="mb-3">
                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                            <input type="date" id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" required>
                        </div>

                        <!-- Bukti Kegiatan -->
                        <div class="mb-3">
                            <label for="bukti_kegiatan" class="form-label">Bukti Kegiatan</label>
                            <input type="file" id="bukti_kegiatan" name="bukti_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" accept=".jpg, .jpeg, .png, .gif" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button id="btnSimpanActivity" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahStep6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Jawaban Tahap 6</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep6" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Evaluasi -->
                    <div class="mb-3">
                        <label for="evaluation" class="form-label">Evaluasi / Saran</label>
                        <textarea id="evaluation" name="evaluation" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan evaluasi" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep6" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>


    <!-- MODAL TAMBAH STEP 1 - NUCLEAR FIX (INLINE JS) -->
    <div class="modal fade" id="modalTambahStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Rumusan Masalah</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep1" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Rumusan Masalah -->
                    <div class="mb-3">
                        <label for="step1_formulation_add" class="form-label">Rumusan Masalah</label>
                        <textarea id="step1_formulation_add" name="step1_formulation" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Rumusan Masalah" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>

                    <!-- Orientasi Masalah (Problem Definition) -->
                    <div class="mb-3">
                        <label for="problem_definition_add" class="form-label">Orientasi Masalah</label>
                        <textarea id="problem_definition_add" name="problem_definition" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Jelaskan orientasi dan latar belakang masalah" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4"></textarea>
                    </div>

                    <!-- Analisis & Indikator (Visual Repeater - Add) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Indikator Penyebab Masalah</label>
                        <p class="text-muted small mb-2">Tambahkan minimal 1 indikator penyebab masalah beserta analisis Anda.</p>
                        
                        <!-- Hidden field for final JSON -->
                        <textarea name="analysis_data" id="analysis_data_add" style="display:none;"></textarea>
                        
                        <!-- Container for indicator rows -->
                        <div id="indicator-container-add" class="mb-3">
                            <!-- Initial row will be added by JavaScript -->
                        </div>
                        
                        <!-- Add button -->
                        <button type="button" id="btn-add-indicator-add" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Indikator Masalah
                        </button>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep1" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getRowTemplate(index) {
            return '<div class="indicator-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #28a745 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-success row-number">Indikator #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-indicator">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Indikator Penyebab</label>' +
                    '<input type="text" class="form-control indicator-cause" placeholder="Contoh: Kurangnya pemahaman siswa" required>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Analisis & Sumber Referensi</label>' +
                    '<textarea class="form-control indicator-analysis" rows="2" placeholder="Jelaskan analisis dan sumber referensi"></textarea>' +
                '</div>' +
            '</div>';
        }

        function updateRowNumbers() {
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Indikator #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-indicator');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addIndicatorRow = function() {
            var container = document.getElementById('indicator-container-add');
            var currentRows = container.querySelectorAll('.indicator-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getRowTemplate(newIndex));
            updateRowNumbers();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-indicator');
            deleteBtn.addEventListener('click', function() {
                removeIndicatorRow(this);
            });
        };

        function removeIndicatorRow(btn) {
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.indicator-row');
                row.remove();
                updateRowNumbers();
            }
        }

        function serializeIndicators() {
            var indicators = [];
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            
            rows.forEach(function(row) {
                var cause = row.querySelector('.indicator-cause').value;
                var analysis = row.querySelector('.indicator-analysis').value;
                if (cause && cause.trim()) {
                    indicators.push({
                        indicator: cause.trim(),
                        analysis: analysis ? analysis.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(indicators);
        }

        var modalElement = document.getElementById('modalTambahStep1');
        modalElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('indicator-container-add');
            container.innerHTML = '';
            addIndicatorRow();
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btn-add-indicator-add').addEventListener('click', addIndicatorRow);
            
            document.getElementById('btnSimpanStep1').addEventListener('click', function() {
                var formulation = document.getElementById('step1_formulation_add').value;
                if (!formulation || !formulation.trim()) {
                    alert('Harap lengkapi rumusan masalah.');
                    return;
                }
                
                var jsonPayload = serializeIndicators();
                document.getElementById('analysis_data_add').value = jsonPayload;
                
                var formData = new FormData(document.getElementById('formTambahDataStep1'));
                
                fetch('formtambahDataStep1.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        document.getElementById('formTambahDataStep1').reset();
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep1'));
                        modal.hide();
                        location.reload();
                    });
                })
                .catch(function(error) {
                    alert('Terjadi kesalahan saat menyimpan data.');
                });
            });
        });
    })();
    </script>

    <div class="modal fade" id="modalEditStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Rumusan Masalah</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahStep1" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <!-- Rumusan Masalah -->
                        <div class="mb-3">
                            <label for="step1_formulation" class="form-label">Rumusan Masalah</label>
                            <textarea id="step1_formulation" name="step1_formulation" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Tambahkan Deskripsi Proyek" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4" required><?php echo isset($step->step1_formulation) ? $step->step1_formulation : ''; ?></textarea>
                        </div>

                        <!-- Orientasi Masalah (Problem Definition) -->
                        <div class="mb-3">
                            <label for="problem_definition" class="form-label">Orientasi Masalah</label>
                            <textarea id="problem_definition" name="problem_definition" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Jelaskan orientasi dan latar belakang masalah" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4"><?php echo isset($step->problem_definition) ? $step->problem_definition : ''; ?></textarea>
                        </div>

                        <!-- Analisis & Indikator (Visual Repeater - Edit) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Indikator Penyebab Masalah</label>
                            <p class="text-muted small mb-2">Edit indikator penyebab masalah dan analisis Anda.</p>
                            
                            <!-- Hidden field for final JSON - ID must match JS -->
                            <textarea name="analysis_data" id="hidden_analysis_data_edit" style="display:none;"><?php echo isset($step->analysis_data) ? htmlspecialchars($step->analysis_data) : ''; ?></textarea>
                            
                            <!-- Container for indicator rows - class must be .indicator-container -->
                            <div id="indicator-container-edit" class="indicator-container mb-3">
                                <!-- Rows will be generated by JavaScript based on existing data -->
                            </div>
                            
                            <!-- Add button - class must be .btn-add-indicator -->
                            <button type="button" class="btn btn-outline-success btn-sm btn-add-indicator">
                                <i class="fas fa-plus"></i> Tambah Indikator Masalah
                            </button>
                        </div>

                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep1" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getEditRowTemplate(index) {
            return '<div class="indicator-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #28a745 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-success row-number">Indikator #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-indicator-edit">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Indikator Penyebab</label>' +
                    '<input type="text" class="form-control indicator-cause" placeholder="Contoh: Kurangnya pemahaman siswa" required>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Analisis & Sumber Referensi</label>' +
                    '<textarea class="form-control indicator-analysis" rows="2" placeholder="Jelaskan analisis dan sumber referensi"></textarea>' +
                '</div>' +
            '</div>';
        }

        function updateEditRowNumbers() {
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Indikator #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-indicator-edit');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addIndicatorRowEdit = function() {
            var container = document.getElementById('indicator-container-edit');
            var currentRows = container.querySelectorAll('.indicator-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getEditRowTemplate(newIndex));
            updateEditRowNumbers();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-indicator-edit');
            deleteBtn.addEventListener('click', function() {
                removeIndicatorRowEdit(this);
            });
        };

        function removeIndicatorRowEdit(btn) {
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.indicator-row');
                row.remove();
                updateEditRowNumbers();
            }
        }

        function serializeEditIndicators() {
            var indicators = [];
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            
            rows.forEach(function(row) {
                var cause = row.querySelector('.indicator-cause').value;
                var analysis = row.querySelector('.indicator-analysis').value;
                if (cause && cause.trim()) {
                    indicators.push({
                        indicator: cause.trim(),
                        analysis: analysis ? analysis.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(indicators);
        }

        var modalEditElement = document.getElementById('modalEditStep1');
        modalEditElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('indicator-container-edit');
            var hiddenField = document.getElementById('hidden_analysis_data_edit');
            var jsonData = hiddenField.value;
            
            container.innerHTML = '';
            
            if (jsonData && jsonData.trim()) {
                try {
                    var indicators = JSON.parse(jsonData);
                    if (Array.isArray(indicators) && indicators.length > 0) {
                        indicators.forEach(function(item, index) {
                            container.insertAdjacentHTML('beforeend', getEditRowTemplate(index + 1));
                            var row = container.querySelectorAll('.indicator-row')[index];
                            row.querySelector('.indicator-cause').value = item.indicator || '';
                            row.querySelector('.indicator-analysis').value = item.analysis || '';
                            
                            row.querySelector('.btn-remove-indicator-edit').addEventListener('click', function() {
                                removeIndicatorRowEdit(this);
                            });
                        });
                        updateEditRowNumbers();
                        return;
                    }
                } catch (e) {
                    // If JSON parsing fails, show empty row
                }
            }
            
            addIndicatorRowEdit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var btnAddEdit = document.querySelector('#modalEditStep1 .btn-add-indicator');
            if (btnAddEdit) {
                btnAddEdit.addEventListener('click', addIndicatorRowEdit);
            }
            
            var btnSaveEdit = document.getElementById('btnUpdatedStep1');
            if (btnSaveEdit) {
                btnSaveEdit.addEventListener('click', function() {
                    var jsonPayload = serializeEditIndicators();
                    document.getElementById('hidden_analysis_data_edit').value = jsonPayload;
                    
                    var form = document.getElementById('formUbahStep1');
                    var formData = new FormData(form);
                    var urlEncodedData = new URLSearchParams(formData).toString();
                    
                    fetch('formeditDataStep1.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: urlEncodedData
                    })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data berhasil disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            form.reset();
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditStep1'));
                            modal.hide();
                            location.reload();
                        });
                    })
                    .catch(function(error) {
                        alert('Terjadi kesalahan saat menyimpan data.');
                    });
                });
            }
        });
    })();
    </script>

    <!-- MODAL EDIT STEP 2 - JADWAL PROYEK (INLINE JS) -->
    <div class="modal fade" id="modalEditStep2" tabindex="-1" role="dialog" aria-labelledby="modalEditStep2Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalEditStep2Label">Edit Jadwal Proyek</h5>
              </div>
              <div class="modal-body">

                <form id="formUbahStep2" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Hidden field for final JSON - pre-filled with existing data -->
                    <textarea name="planning_data" id="planning_data_edit" style="display:none;"><?php echo isset($step->planning_data) ? htmlspecialchars($step->planning_data) : ''; ?></textarea>

                    <!-- Schedule Rows Container -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jadwal Kegiatan Proyek</label>
                        <p class="text-muted small mb-2">Edit kegiatan proyek beserta jadwal dan penanggung jawab.</p>
                        
                        <!-- Container for schedule rows - class must match JS -->
                        <div id="schedule-container-edit" class="schedule-container mb-3">
                            <!-- Rows will be generated by JavaScript based on existing data -->
                        </div>
                        
                        <!-- Add button - class must match JS -->
                        <button type="button" class="btn btn-outline-success btn-sm btn-add-schedule-edit">
                            <i class="fas fa-plus"></i> Tambah Kegiatan
                        </button>
                    </div>

                </form>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep2" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getScheduleRowTemplateEdit(index) {
            return '<div class="schedule-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #17a2b8 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-info row-number">Kegiatan #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-schedule-edit">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Nama Kegiatan</label>' +
                    '<input type="text" class="form-control schedule-task" placeholder="Contoh: Riset Awal" required>' +
                '</div>' +
                '<div class="row mb-2">' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Mulai</label>' +
                        '<input type="date" class="form-control schedule-start">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Selesai</label>' +
                        '<input type="date" class="form-control schedule-end">' +
                    '</div>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Penanggung Jawab (PIC)</label>' +
                    '<input type="text" class="form-control schedule-pic" placeholder="Nama anggota yang bertanggung jawab">' +
                '</div>' +
            '</div>';
        }

        function updateScheduleRowNumbersEdit() {
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Kegiatan #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-schedule-edit');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addScheduleRowEdit = function() {
            var container = document.getElementById('schedule-container-edit');
            var currentRows = container.querySelectorAll('.schedule-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateEdit(newIndex));
            updateScheduleRowNumbersEdit();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-schedule-edit');
            deleteBtn.addEventListener('click', function() {
                removeScheduleRowEdit(this);
            });
        };

        function removeScheduleRowEdit(btn) {
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.schedule-row');
                row.remove();
                updateScheduleRowNumbersEdit();
            }
        }

        function serializeScheduleEdit() {
            var schedule = [];
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            
            rows.forEach(function(row) {
                var task = row.querySelector('.schedule-task').value;
                var startDate = row.querySelector('.schedule-start').value;
                var endDate = row.querySelector('.schedule-end').value;
                var pic = row.querySelector('.schedule-pic').value;
                
                if (task && task.trim()) {
                    schedule.push({
                        task: task.trim(),
                        start_date: startDate || '',
                        end_date: endDate || '',
                        pic: pic ? pic.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(schedule);
        }

        // Initialize edit modal with existing data on open
        var modalEditElement = document.getElementById('modalEditStep2');
        modalEditElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('schedule-container-edit');
            var hiddenField = document.getElementById('planning_data_edit');
            var jsonData = hiddenField.value;
            
            container.innerHTML = '';
            
            if (jsonData && jsonData.trim()) {
                try {
                    var scheduleData = JSON.parse(jsonData);
                    if (Array.isArray(scheduleData) && scheduleData.length > 0) {
                        scheduleData.forEach(function(item, index) {
                            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateEdit(index + 1));
                            var row = container.querySelectorAll('.schedule-row')[index];
                            row.querySelector('.schedule-task').value = item.task || '';
                            row.querySelector('.schedule-start').value = item.start_date || '';
                            row.querySelector('.schedule-end').value = item.end_date || '';
                            row.querySelector('.schedule-pic').value = item.pic || '';
                            
                            row.querySelector('.btn-remove-schedule-edit').addEventListener('click', function() {
                                removeScheduleRowEdit(this);
                            });
                        });
                        updateScheduleRowNumbersEdit();
                        return;
                    }
                } catch (e) {
                    // If JSON parsing fails, show empty row
                    console.log('Error parsing JSON:', e);
                }
            }
            
            // If no data or parse error, add empty row
            addScheduleRowEdit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var btnAddEdit = document.querySelector('#modalEditStep2 .btn-add-schedule-edit');
            if (btnAddEdit) {
                btnAddEdit.addEventListener('click', addScheduleRowEdit);
            }
            
            var btnSaveEdit = document.getElementById('btnUpdatedStep2');
            if (btnSaveEdit) {
                btnSaveEdit.addEventListener('click', function() {
                    var container = document.getElementById('schedule-container-edit');
                    var rows = container.querySelectorAll('.schedule-row');
                    var hasValidRow = false;
                    
                    rows.forEach(function(row) {
                        var task = row.querySelector('.schedule-task').value;
                        if (task && task.trim()) {
                            hasValidRow = true;
                        }
                    });
                    
                    if (!hasValidRow) {
                        alert('Harap tambahkan minimal satu kegiatan dengan nama kegiatan.');
                        return;
                    }
                    
                    var jsonPayload = serializeScheduleEdit();
                    document.getElementById('planning_data_edit').value = jsonPayload;
                    
                    var form = document.getElementById('formUbahStep2');
                    var formData = new FormData(form);
                    
                    fetch('formeditDataStep2.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Jadwal berhasil diperbarui!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            form.reset();
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditStep2'));
                            modal.hide();
                            location.reload();
                        });
                    })
                    .catch(function(error) {
                        alert('Terjadi kesalahan saat menyimpan jadwal.');
                    });
                });
            }
        });
    })();
    </script>

    <!-- MODAL TAMBAH STEP 3 - LOGBOOK PELAKSANAAN (INLINE JS) -->
    <div class="modal fade" id="modalTambahStep3" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep3Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep3Label">Tambah Catatan Logbook</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep3" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Hidden field for final JSON (full array with appended entry) -->
                    <textarea name="logbook_data" id="logbook_data_hidden" style="display:none;"><?php echo isset($step->logbook_data) ? htmlspecialchars($step->logbook_data) : '[]'; ?></textarea>

                    <!-- Logbook Entry Form -->
                    <div class="mb-3">
                        <label for="logbook_date" class="form-label fw-bold">Tanggal Kegiatan <span class="text-danger">*</span></label>
                        <input type="date" id="logbook_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="logbook_activity" class="form-label fw-bold">Kegiatan yang Dilakukan <span class="text-danger">*</span></label>
                        <textarea id="logbook_activity" class="form-control" rows="3" placeholder="Contoh: Melakukan riset awal, menyusun outline proposal, dll." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="logbook_obstacles" class="form-label fw-bold">Kendala / Hambatan</label>
                        <textarea id="logbook_obstacles" class="form-control" rows="2" placeholder="Contoh: Sulit menemukan referensi, keterbatasan waktu, dll. Isi dengan '-' jika tidak ada kendala."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="logbook_progress" class="form-label fw-bold">Progres Keseluruhan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="logbook_progress" class="form-control" min="0" max="100" value="0" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Perkiraan progres total proyek setelah kegiatan ini (0-100%)</small>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep3" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        // Get existing logbook data from PHP (hydrated on page load)
        var existingLogbookData = [];
        try {
            var hiddenField = document.getElementById('logbook_data_hidden');
            if (hiddenField && hiddenField.value && hiddenField.value.trim()) {
                existingLogbookData = JSON.parse(hiddenField.value);
                if (!Array.isArray(existingLogbookData)) {
                    existingLogbookData = [];
                }
            }
        } catch (e) {
            console.log('Error parsing existing logbook data:', e);
            existingLogbookData = [];
        }

        // Reset form when modal opens
        var modalElementStep3 = document.getElementById('modalTambahStep3');
        if (modalElementStep3) {
            modalElementStep3.addEventListener('shown.bs.modal', function() {
                document.getElementById('logbook_date').value = new Date().toISOString().split('T')[0];
                document.getElementById('logbook_activity').value = '';
                document.getElementById('logbook_obstacles').value = '';
                document.getElementById('logbook_progress').value = existingLogbookData.length > 0 
                    ? (existingLogbookData[existingLogbookData.length - 1].progress || 0)
                    : 0;
            });
        }

        // Add logbook entry function (APPEND MODE)
        window.addLogbookEntry = function() {
            var dateVal = document.getElementById('logbook_date').value;
            var activityVal = document.getElementById('logbook_activity').value.trim();
            var obstaclesVal = document.getElementById('logbook_obstacles').value.trim() || '-';
            var progressVal = parseInt(document.getElementById('logbook_progress').value) || 0;

            // Validation
            if (!dateVal) {
                alert('Harap isi tanggal kegiatan.');
                return false;
            }
            if (!activityVal) {
                alert('Harap isi kegiatan yang dilakukan.');
                return false;
            }
            if (progressVal < 0 || progressVal > 100) {
                alert('Progres harus antara 0-100%.');
                return false;
            }

            // Create new entry
            var newEntry = {
                date: dateVal,
                activity: activityVal,
                obstacles: obstaclesVal,
                progress: progressVal,
                created_at: new Date().toISOString()
            };

            // Append to existing data (CRUCIAL: APPEND MODE)
            existingLogbookData.push(newEntry);

            // Update hidden field with FULL array
            document.getElementById('logbook_data_hidden').value = JSON.stringify(existingLogbookData);

            return true;
        };

        document.addEventListener('DOMContentLoaded', function() {
            var btnSaveStep3 = document.getElementById('btnSimpanStep3');
            if (btnSaveStep3) {
                btnSaveStep3.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Add entry to array and serialize
                    if (!addLogbookEntry()) {
                        return;
                    }
                    
                    var formData = new FormData(document.getElementById('formTambahDataStep3'));
                    
                    fetch('formtambahDataStep3.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Catatan logbook berhasil disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep3'));
                            modal.hide();
                            location.reload();
                        });
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan logbook.');
                    });
                });
            }
        });
    })();
    </script>

    <div class="modal fade" id="modalEditStep6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Evaluasi</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahStep6" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <div class="mb-3">
                            <label for="evaluation" class="form-label">Evaluasi / Saran</label>
                            <textarea id="evaluation" name="evaluation" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Tambahkan evaluasi" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4" required><?php echo $project_data->evaluation; ?></textarea>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep6" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalLihatKelompok" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Pilih Kelompok Untuk Dilihat</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formLihatKelompok" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <div class="mb-3 d-flex flex-column">
                            <label for="lihat_kelompok" class="form-label">Pilih kelompok yang ingin dilihat</label>
                            
                            <select id="lihat_kelompok" name="lihat_kelompok" 
                                    class="form-select rounded-2 px-3 py-2 mt-2" 
                                    style="color:#000000;" required>
                                <option value="">Pilih No Kelompok</option>
                                <?php
                                foreach ($filtered_records as $record) {
                                    echo '<option value="' . $record->group_project . '">Kelompok ' . $record->group_number . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnSubmitLihat" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">lihat</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>