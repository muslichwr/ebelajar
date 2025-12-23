<?php

require_once('../../config.php');

redirect_if_major_upgrade_required();

require_login();

global $DB, $USER;

$user_id = $USER->id;

$cmid = required_param('id', PARAM_INT);

if (!$cm) {
    print_error('invalidcoursemodule');
}
$ebelajar = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
$groupproject_records = $DB->get_records('groupproject', ['ebelajar' => $ebelajar]);
$total_grup = $DB->get_field('ebelajar', 'total_grup', ['coursemoduleid' => $cmid]);
$step3_schedule_image = $DB->get_field('ebelajar', 'step3_schedule_image', ['coursemoduleid' => $cmid]);
$step3_schedule_file = $DB->get_field('ebelajar', 'step3_schedule_file', ['coursemoduleid' => $cmid]);

?>

<?php
    include 'logicscript.php';
  ?>

<style>
    :root {
        --custom-blue: #bed4d1;
        --custom-green: #5a9f68;
        --custom-red: #ff5757;
        /* Step Colors */
        --step1-color: #10b981;
        --step2-color: #3b82f6;
        --step3-color: #06b6d4;
        --step5-color: #8b5cf6;
        --step6-color: #f97316;
        --step7-color: #eab308;
        --step8-color: #ec4899;
    }
    .logo {
        display: inline-block;
    }
    /* Premium Card Styles */
    .step-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .step-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    /* Group Card Styles */
    .group-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-left: 5px solid #3b82f6;
    }
    .group-card:hover {
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.25);
        transform: translateY(-3px);
    }
    .group-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: white;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.2rem;
        margin-right: 1rem;
    }
    /* Progress Bar Enhancement */
    .progress-enhanced {
        height: 12px;
        border-radius: 8px;
        background-color: #e5e7eb;
        overflow: hidden;
    }
    .progress-enhanced .progress-bar {
        background: linear-gradient(90deg, #3b82f6, #6366f1);
        border-radius: 8px;
        transition: width 0.6s ease;
    }
    /* Premium Alert */
    .premium-alert {
        border-radius: 8px;
        border-left: 4px solid;
    }
    .premium-alert.alert-warning { border-left-color: #f59e0b; }
    .premium-alert.alert-info { border-left-color: #3b82f6; }
</style>

<script>
    $(document).ready(function() {
        $('#btnSimpanStep3').click(function() {
            var formData = new FormData($('#formTambahDataStep3')[0]);
            var file_step3 = $('[name="file_step3"]').prop('files')[0];
            var file_image = $('[name="file_image"]').prop('files')[0];

            if (!file_step3 || !file_image) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataStep3.php',
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
                        $('#formTambahDataStep3')[0].reset();
                        $('#modalTambahStep3').modal('hide');
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
        $('#btnUpdateStep3').click(function() {
            var form = $(this).closest('.modal').find('form');

            var formData = new FormData(form[0]);
            
            $.ajax({
                url: 'formeditDataStep3.php',
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
                        $('#formEditDataStep3')[0].reset();
                        $('#modalEditStep3').modal('hide');
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

    <!-- Navbar -->
    <nav class="container navbar navbar-expand-md navbar-light px-2 shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); border-radius: 0 0 12px 12px;">
        <div class="container-fluid px-md-5">
            <div class="logo d-flex align-items-center gap-2 mx-auto">
                <i class="fas fa-layer-group fs-3 text-white"></i>
                <h3 class="navbar-brand text-white fw-bolder mb-0">Kelompok Dashboard</h3>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <div class="container mx-auto p-3 mt-3">
        <div class="row align-items-center p-4 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px;">
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <span class="badge bg-light text-primary mb-2"><i class="fas fa-users me-1"></i> Monitoring Kelompok</span>
                <h2 class="fw-bolder text-white mb-2">Pilih Kelompok</h2>
                <p class="text-white-50">Silahkan memilih kelompok untuk melihat aktivitas kelompok yang telah dilakukan dalam proses pembelajaran.</p>
            </div>
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(2).png" class="img-fluid" alt="Ilustrasi" style="max-height: 180px;">
            </div>
        </div>
    </div>

    <!-- Schedule Card -->
    <div class="container mx-auto p-3" id="dataProjectContainer">
        <div class="row">
            <div class="col-12">
                <?php if ($step3_schedule_image && $step3_schedule_file != null): ?>
                    <!-- Schedule Card with Data -->
                    <div class="card step-card" style="border-top: 4px solid #3b82f6;">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Jadwal Proyek Untuk Siswa</h5>
                        </div>
                        <div class="card-body" style="background-color: #f8fafc;">
                            <img src="<?php echo(new moodle_url('/mod/ebelajar/' . $step3_schedule_image))->out(); ?>" alt="gambar jadwal perencanaan" class="d-block mx-auto rounded shadow-sm" style="max-width: 60%;">
                            <?php if (!empty($step3_schedule_file)): ?>
                                <div class="text-center mt-3">
                                    <a href="<?php echo $step3_schedule_file; ?>" download class="btn btn-outline-success rounded-pill">
                                        <i class="fas fa-download me-1"></i> Download: <?php echo basename($step3_schedule_file); ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center mt-3"><i class="fas fa-file-alt me-1"></i> Tidak ada file yang diunggah.</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light border-0 text-end">
                            <button data-bs-toggle="modal" data-bs-target="#modalEditStep3" class="btn btn-outline-warning rounded-pill px-4">
                                <i class="fas fa-edit me-1"></i> Edit Data
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahStep3">
                            <i class="fas fa-plus me-1"></i> Tambah Data
                        </button>
                    </div>
                    <div class="alert alert-warning premium-alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Anda belum menambahkan jadwal proyek untuk siswa.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalTambahStep3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Step 3</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep3" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">
                    <!-- File image -->
                    <div class="mb-3">
                        <label for="file_image" class="form-label">Gambar Thumnail Jadwal</label>
                        <input type="file" id="file_image" name="file_image" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>

                    <!-- File Step3 -->
                    <div class="mb-3">
                        <label for="file_step3" class="form-label">File Lampiran</label>
                        <input type="file" id="file_step3" name="file_step3" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
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

    <div class="modal fade" id="modalEditStep3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Step 3</h5>
              </div>
              <div class="modal-body">
                <form id="formEditDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">

                    <!-- File Gambar -->
                    <div class="mb-3">
                        <label for="file_image" class="form-label">Gambar Thumnail Jadwal</label>
                        <input type="file" id="file_image" name="file_image" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>

                    <!-- File Step3 -->
                    <div class="mb-3">
                        <label for="file_step3" class="form-label">File Lampiran</label>
                        <input type="file" id="file_step3" name="file_step3" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnUpdateStep3" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Update</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <?php
    foreach ($groupproject_records as $groupproject) {
        // Ambil ID grup proyek
        $group_project = $groupproject->id;

        // Ambil status langkah proyek
        $step1_status = $DB->get_field('project', 'status_step1', ['group_project' => $group_project]);
        $step2_status = $DB->get_field('project', 'status_step2', ['group_project' => $group_project]);
        $step3_status = $DB->get_field('project', 'status_step3', ['group_project' => $group_project]);
        $step4_status = $DB->get_field('project', 'status_step4', ['group_project' => $group_project]);
        $step5_status = $DB->get_field('project', 'status_step5', ['group_project' => $group_project]);
        $step6_status = $DB->get_field('project', 'status_step6', ['group_project' => $group_project]);
        $step7_status = $DB->get_field('project', 'status_step7', ['group_project' => $group_project]);
        $step8_status = $DB->get_field('project', 'status_step8', ['group_project' => $group_project]);

        // Hitung jumlah langkah yang selesai
        $completed_steps = 0;
        if ($step1_status == "Selesai") $completed_steps++;
        if ($step2_status == "Selesai") $completed_steps++;
        if ($step3_status == "Selesai") $completed_steps++;
        if ($step4_status == "Selesai") $completed_steps++;
        if ($step5_status == "Selesai") $completed_steps++;
        if ($step6_status == "Selesai") $completed_steps++;
        if ($step7_status == "Selesai") $completed_steps++;
        if ($step8_status == "Selesai") $completed_steps++;

        // Hitung total langkah
        $total_steps = 8;

        $percentage = ($completed_steps / $total_steps) * 100;

        echo '
        <!-- Group Card -->
        <div class="container mx-auto p-3">
            <div class="group-card p-4">
                <div class="row align-items-center">
                    <!-- Group Info Column -->
                    <div class="col-12 col-md-8 mb-3 mb-md-0">
                        <div class="d-flex align-items-center mb-3">
                            <div class="group-badge">' . $groupproject->group_number . '</div>
                            <div>
                                <h4 class="fw-bold mb-0" style="color: #333;">Kelompok ' . $groupproject->group_number . '</h4>
                                <small class="text-muted"><i class="fas fa-users me-1"></i>Project Team</small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold" style="color: #333;"><i class="fas fa-tasks me-1"></i>Progress</span>
                                <span class="badge bg-primary text-white">' . $completed_steps . '/' . $total_steps . ' Tahap</span>
                            </div>
                            <div class="progress progress-enhanced">
                                <div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">' . round($percentage) . '% Selesai</small>
                        </div>
                    </div>
                    <!-- Action Column -->
                    <div class="col-12 col-md-4 text-center text-md-end">
                        <button class="btn btn-outline-primary rounded-pill px-4" onclick="lihat(' . $groupproject->id . ', ' . $cmid . ')">
                            <i class="fas fa-eye me-1"></i> Lihat Kelompok
                        </button>
                    </div>
                </div>
            </div>
        </div>
        ';
    }
    ?>
