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
    $step7_status = $DB->get_field('project', 'status_step7', ['group_project' => $group_project]);
    $step8_status = $DB->get_field('project', 'status_step8', ['group_project' => $group_project]);
} else {
    $project_data = null;
}

echo '
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
    .step-card.step-1 { border-top: 4px solid var(--step1-color); }
    .step-card.step-2 { border-top: 4px solid var(--step2-color); }
    .step-card.step-3 { border-top: 4px solid var(--step3-color); }
    .step-card.step-5 { border-top: 4px solid var(--step5-color); }
    .step-card.step-6 { border-top: 4px solid var(--step6-color); }
    .step-card.step-7 { border-top: 4px solid var(--step7-color); }
    .step-card.step-8 { border-top: 4px solid var(--step8-color); }
    
    /* Step Header */
    .step-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
    }
    .step-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .step-title i { font-size: 1.25rem; }
    .step-title span:last-child { font-size: 1.1rem; font-weight: 600; color: #333; }
    
    /* Status Badges */
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .status-badge.filled { background-color: #3b82f6; color: #ffffff; }
    .status-badge.empty { background-color: #f3f4f6; color: #6b7280; }
    .status-badge.warning { background-color: #fef3c7; color: #92400e; }
    
    /* Premium Table */
    .premium-table {
        border-radius: 8px;
        overflow: hidden;
        border: none;
    }
    .premium-table thead th {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: white;
        font-weight: 600;
        border: none;
        padding: 0.85rem;
    }
    .premium-table tbody tr:hover { background-color: rgba(59, 130, 246, 0.08); }
    .premium-table tbody td { border-color: #e5e7eb; padding: 0.75rem; vertical-align: middle; }
    
    /* Teacher Action Card - Highlighted for grading */
    .teacher-action-card {
        border: 2px solid #fbbf24;
        border-radius: 12px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    }
    .teacher-action-card .card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6b7280;
    }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
    
    /* Premium Alert */
    .premium-alert {
        border-radius: 8px;
        border-left: 4px solid;
    }
    .premium-alert.alert-warning { border-left-color: #f59e0b; }
    .premium-alert.alert-info { border-left-color: #3b82f6; }
    .premium-alert.alert-secondary { border-left-color: #6b7280; }
    
    /* Info Panel for student data */
    .info-panel {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
    }
</style>
';

echo '
    <nav class="container navbar navbar-expand-md navbar-light px-2 shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); border-radius: 0 0 12px 12px;">
        <div class="container-fluid px-md-5">
            <div class="logo d-flex align-items-center gap-2 mx-auto">
                <i class="fas fa-chalkboard-teacher fs-3 text-white"></i>
                <h3 class="navbar-brand text-white fw-bolder mb-0">Teacher Monitoring</h3>
            </div>
        </div>
    </nav>
';


echo '
    <!-- Group Header Banner -->
    <div class="container mx-auto p-3 mt-3">
        <div class="row align-items-center p-4 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px;">
            <div class="col-lg-5 col-12 text-center mb-4 mb-lg-0">
                <img src="assets/guru-awal(3).png" class="img-fluid" alt="Ilustrasi" style="max-height: 180px;">
            </div>
            <div class="col-lg-7 col-12 text-center text-lg-start">
                <span class="badge bg-light text-primary mb-2"><i class="fas fa-users me-1"></i> Monitoring Kelompok</span>
                <h1 class="fw-bolder text-white mb-0">KELOMPOK ' . htmlspecialchars($groups_number) . '</h1>
            </div>
        </div>
    </div>
';


echo' 
    <div class="container mx-auto p-3">
        <div class="d-flex justify-content-start">
            <button class="btn d-flex align-items-center gap-2 rounded-pill px-4 shadow-sm" onclick="kembalimenu1('.$cmid.')" style="background-color: white; color: #333; border: 1px solid #e5e7eb;">
                <i class="fas fa-arrow-left"></i>Kembali ke Menu
            </button>
        </div>
    </div>
';

if ($student_records) {
    echo '
    <!-- Student List Card -->
    <div class="container mx-auto p-3">
        <div class="card step-card" style="border-top: 4px solid #3b82f6;">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);">
                <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Anggota Kelompok</h5>
            </div>
            <div class="card-body" style="background-color: #f8fafc;">
                <table class="table table-hover premium-table bg-white mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Siswa</th>
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
                <td class="text-center fw-bold">' . $no++ . '</td>
                <td><i class="fas fa-user-circle text-primary me-2"></i>' . $nama_user . '</td>
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
        <div class="alert alert-warning premium-alert">
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p class="mb-0">Tidak ada data siswa dalam kelompok ini.</p>
            </div>
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
            <table class="table table-hover premium-table bg-white">
                <thead>
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
        $indicators_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada indikator yang ditambahkan.</div>';
    }
} else {
    $indicators_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada indikator yang ditambahkan.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep1">
    <div class="row">
        <div class="col-12">' .
            ($step1_status == "Selesai" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">1</span>
                        <i class="fas fa-brain" style="color: var(--step1-color);"></i>
                        <span>Rumusan Masalah & Analisis</span>
                    </div>
                    <span class="status-badge filled"><i class="fas fa-check-circle"></i> Sudah Diisi</span>
                </div>
                <div class="card step-card step-1">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Rumusan Masalah & Analisis</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        <div class="info-panel mb-3">
                            <p class="mb-1"><strong><i class="fas fa-book-open me-2 text-primary"></i>Studi Kasus:</strong></p>
                            <p class="mb-0">' . htmlspecialchars($ebelajar_records->case_study) . '</p>
                        </div>
                        
                        <div class="info-panel mb-3">
                            <p class="mb-1"><strong><i class="fas fa-question-circle me-2 text-primary"></i>Rumusan Masalah:</strong></p>
                            <div class="bg-white p-3 rounded">' . 
                                (!empty($step->step1_formulation) ? 
                                    nl2br(htmlspecialchars($step->step1_formulation)) : 
                                    '<span class="badge bg-warning text-dark">Belum ada rumusan masalah</span>'
                                ) . 
                            '</div>
                        </div>
                        
                        <div class="info-panel mb-3">
                            <p class="mb-1"><strong><i class="fas fa-compass me-2 text-info"></i>Orientasi Masalah:</strong></p>
                            <div class="bg-white p-3 rounded">' . 
                                (!empty($step->problem_definition) ? 
                                    nl2br(htmlspecialchars($step->problem_definition)) : 
                                    '<span class="badge bg-warning text-dark">Belum ada orientasi masalah</span>'
                                ) . 
                            '</div>
                        </div>
                        
                        <p><strong><i class="fas fa-list-check me-2 text-primary"></i>Indikator Penyebab Masalah:</strong></p>
                        ' . $indicators_html . '
                    </div>
                </div>' : 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">1</span>
                        <i class="fas fa-brain" style="color: var(--step1-color);"></i>
                        <span>Rumusan Masalah & Analisis</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-clock"></i> Belum Diisi</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 1.
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
            <table class="table table-hover premium-table bg-white">
                <thead>
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
        $schedule_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada jadwal yang disusun.</div>';
    }
} else {
    $schedule_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada jadwal yang disusun.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep2">
    <div class="row">
        <div class="col-12">' .
            ($step2_status == "Selesai" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">2</span>
                        <i class="fas fa-calendar-alt" style="color: var(--step2-color);"></i>
                        <span>Perencanaan & Jadwal Proyek</span>
                    </div>
                    <span class="status-badge filled"><i class="fas fa-check-circle"></i> Sudah Diisi</span>
                </div>
                <div class="card step-card step-2">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Perencanaan & Jadwal Proyek</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        <p><strong><i class="fas fa-list me-2 text-primary"></i>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
                    </div>
                </div>' : 
            ($step2_status == "Mengerjakan" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">2</span>
                        <i class="fas fa-calendar-alt" style="color: var(--step2-color);"></i>
                        <span>Perencanaan & Jadwal Proyek</span>
                    </div>
                    <span class="status-badge warning"><i class="fas fa-spinner"></i> Sedang Dikerjakan</span>
                </div>
                <div class="card step-card step-2">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Perencanaan & Jadwal Proyek</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        <p><strong><i class="fas fa-list me-2 text-primary"></i>Jadwal pelaksanaan proyek kelompok:</strong></p>
                        ' . $schedule_html . '
                    </div>
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">2</span>
                        <i class="fas fa-calendar-alt" style="color: var(--step2-color);"></i>
                        <span>Perencanaan & Jadwal Proyek</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 2.
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
            <table class="table table-hover premium-table bg-white">
                <thead>
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
        $logbook_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada catatan logbook.</div>';
    }
} else {
    $logbook_html = '<div class="alert alert-info premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada catatan logbook.</div>';
}

echo '
<div class="container mx-auto p-3" id="dataStep3">
    <div class="row">
        <div class="col-12">' .
            ($step3_status == "Selesai" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">4</span>
                        <i class="fas fa-book-open" style="color: var(--step3-color);"></i>
                        <span>Logbook Pelaksanaan</span>
                    </div>
                    <span class="status-badge filled"><i class="fas fa-check-circle"></i> Sudah Diisi</span>
                </div>
                <div class="card step-card step-3">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Logbook Pelaksanaan Proyek</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        <p><strong><i class="fas fa-history me-2 text-info"></i>Catatan harian pelaksanaan proyek kelompok:</strong></p>
                        ' . $logbook_html . '
                    </div>
                </div>' : 
            ($step3_status == "Mengerjakan" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">4</span>
                        <i class="fas fa-book-open" style="color: var(--step3-color);"></i>
                        <span>Logbook Pelaksanaan</span>
                    </div>
                    <span class="status-badge warning"><i class="fas fa-spinner"></i> Sedang Dikerjakan</span>
                </div>
                <div class="card step-card step-3">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Logbook Pelaksanaan Proyek</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        <p><strong><i class="fas fa-history me-2 text-info"></i>Catatan harian pelaksanaan proyek kelompok:</strong></p>
                        ' . $logbook_html . '
                    </div>
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">4</span>
                        <i class="fas fa-book-open" style="color: var(--step3-color);"></i>
                        <span>Logbook Pelaksanaan</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 3.
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
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">5</span>
                        <i class="fas fa-box-open" style="color: var(--step5-color);"></i>
                        <span>Pengumpulan Proyek</span>
                    </div>
                    <span class="status-badge filled"><i class="fas fa-check-circle"></i> Sudah Dikumpulkan</span>
                </div>
                <div class="card step-card step-5">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Data Proyek Kelompok</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">' .
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
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">5</span>
                        <i class="fas fa-box-open" style="color: var(--step5-color);"></i>
                        <span>Pengumpulan Proyek</span>
                    </div>
                    <span class="status-badge warning"><i class="fas fa-spinner"></i> Belum Mengumpulkan</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum mengumpulkan proyek.
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">5</span>
                        <i class="fas fa-box-open" style="color: var(--step5-color);"></i>
                        <span>Pengumpulan Proyek</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 4.
                </div>'
            )) .
        '</div>
    </div>
</div>';


echo '
<div class="container mx-auto p-3" id="dataStep6">
    <div class="row">
        <div class="col-12">' .
            ($step6_status == "Selesai" && !empty($step->presentation_data) ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">6</span>
                        <i class="fas fa-chalkboard-teacher" style="color: var(--step6-color);"></i>
                        <span>Presentasi Proyek</span>
                    </div>
                    <span class="status-badge filled"><i class="fas fa-check-circle"></i> Sudah Dikumpulkan</span>
                </div>
                <div class="card step-card step-6">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-presentation me-2"></i>Data Presentasi Kelompok</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">' .
                        (function() use ($step, $cmid, $DB) {
                            $presentation_info = json_decode($step->presentation_data, true);
                            $output = '';
                            
                            if (!empty($presentation_info['link_presentation'])) {
                                $output .= '<p><strong>Link Presentasi:</strong></p>';
                                $output .= '<p><a href="' . htmlspecialchars($presentation_info['link_presentation']) . '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt"></i> Buka Presentasi</a></p>';
                            }
                            
                            if (!empty($presentation_info['filename'])) {
                                $fs = get_file_storage();
                                $cm = get_coursemodule_from_id('ebelajar', $cmid, 0, false, MUST_EXIST);
                                $context = context_module::instance($cm->id);
                                $file = $fs->get_file(
                                    $context->id,
                                    'mod_ebelajar',
                                    'presentation_file',
                                    $step->id,
                                    '/',
                                    $presentation_info['filename']
                                );
                                if ($file && !$file->is_directory()) {
                                    $file_url = moodle_url::make_pluginfile_url(
                                        $context->id,
                                        'mod_ebelajar',
                                        'presentation_file',
                                        $step->id,
                                        '/',
                                        $presentation_info['filename']
                                    );
                                    $output .= '<p><strong>File Presentasi:</strong></p>';
                                    $output .= '<p><a href="' . $file_url . '" class="btn btn-success btn-sm" download><i class="fas fa-download"></i> Download: ' . htmlspecialchars($presentation_info['filename']) . '</a></p>';
                                } else {
                                    $output .= '<p><strong>File Presentasi:</strong> File tidak ditemukan.</p>';
                                }
                            } else {
                                $output .= '<p><strong>File Presentasi:</strong> Tidak ada file yang diunggah.</p>';
                            }
                            
                            if (!empty($presentation_info['notes'])) {
                                $output .= '<p><strong>Catatan Tambahan:</strong></p>';
                                $output .= '<div class="bg-white p-3 rounded mb-3">' . nl2br(htmlspecialchars($presentation_info['notes'])) . '</div>';
                            }
                            
                            $output .= '<p class="text-muted small mt-3"><i class="fas fa-clock"></i> Diunggah: ' . htmlspecialchars($presentation_info['uploaded_at'] ?? '-') . '</p>';
                            return $output;
                        })() .
                    '</div>
                </div>' :
            ($step6_status == "Mengerjakan" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">6</span>
                        <i class="fas fa-chalkboard-teacher" style="color: var(--step6-color);"></i>
                        <span>Presentasi Proyek</span>
                    </div>
                    <span class="status-badge warning"><i class="fas fa-spinner"></i> Belum Mengumpulkan</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum mengumpulkan presentasi.
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">6</span>
                        <i class="fas fa-chalkboard-teacher" style="color: var(--step6-color);"></i>
                        <span>Presentasi Proyek</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 5.
                </div>'
            )) .
        '</div>
    </div>
</div>';





// Decode evaluation_data for Syntax 7
$evaluation_info = [];
if (!empty($step->evaluation_data)) {
    $evaluation_info = json_decode($step->evaluation_data, true);
}

// Get revision file URL if exists
$revision_file_url = null;
$revision_filename = '';
if (!empty($evaluation_info['revision_file'])) {
    $fs = get_file_storage();
    $cm = get_coursemodule_from_id('ebelajar', $cmid, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
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


echo '
<div class="container mx-auto p-3" id="dataStep7">
    <div class="row">
        <div class="col-12">' .
            ($step6_status == "Selesai" ? 
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">7</span>
                        <i class="fas fa-clipboard-check" style="color: var(--step7-color);"></i>
                        <span>Penilaian & Evaluasi</span>
                    </div>
                    <span class="status-badge ' . (!empty($evaluation_info['teacher_feedback']) ? 'filled"><i class="fas fa-check-circle"></i> Feedback Diberikan' : 'warning"><i class="fas fa-pen"></i> Perlu Evaluasi') . '</span>
                </div>
                
                <!-- Step 7 Card -->
                <div class="card step-card step-7">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Evaluasi Proyek Kelompok</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">
                        
                        <!-- Teacher Feedback Section -->
                        <div class="mb-4">
                            <p class="mb-2"><strong><i class="fas fa-comment-dots me-2 text-primary"></i>Feedback Guru:</strong></p>' .
                            (!empty($evaluation_info['teacher_feedback']) ?
                                '<div class="bg-white p-3 rounded border mb-3">' . nl2br(htmlspecialchars($evaluation_info['teacher_feedback'])) . '</div>' :
                                '<div class="alert alert-secondary premium-alert"><i class="fas fa-info-circle me-2"></i>Belum ada feedback. Gunakan tombol di bawah untuk memberikan evaluasi.</div>'
                            ) .
                        '</div>
                        
                        <!-- Action Button -->
                        <div class="mb-4">
                            <button class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalEvaluasiGuru">
                                <i class="fas fa-' . (!empty($evaluation_info['teacher_feedback']) ? 'edit' : 'pen') . ' me-1"></i>' . (!empty($evaluation_info['teacher_feedback']) ? 'Edit Feedback' : 'Berikan Evaluasi') . '
                            </button>
                        </div>
                        
                        <!-- Student Revision Section (Read Only) -->
                        <hr>
                        <p class="mb-2"><strong><i class="fas fa-file-alt me-2 text-info"></i>Revisi Siswa:</strong></p>' .
                        (!empty($evaluation_info['revision_notes']) ?
                            '<div class="bg-white p-3 rounded border mb-3">
                                <p class="mb-1"><strong>Catatan Perbaikan:</strong></p>
                                <p class="mb-0">' . nl2br(htmlspecialchars($evaluation_info['revision_notes'])) . '</p>
                            </div>' :
                            '<div class="alert alert-secondary premium-alert"><i class="fas fa-info-circle me-2"></i>Siswa belum mengirimkan revisi.</div>'
                        ) .
                        ($revision_file_url ?
                            '<p><a href="' . $revision_file_url . '" class="btn btn-success rounded-pill" download><i class="fas fa-download me-1"></i> Download Revisi: ' . htmlspecialchars($revision_filename) . '</a></p>' :
                            ''
                        ) .
                    '</div>
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">7</span>
                        <i class="fas fa-clipboard-check" style="color: var(--step7-color);"></i>
                        <span>Penilaian & Evaluasi</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 6. Evaluasi dapat dilakukan setelah presentasi selesai.
                </div>'
            ) .
        '</div>
    </div>
</div>';

// Modal for Teacher Feedback
echo '
<div class="modal fade" id="modalEvaluasiGuru" tabindex="-1" role="dialog" aria-labelledby="modalEvaluasiGuruLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6, #6366f1); color:#ffffff">
              <h5 class="modal-title" id="modalEvaluasiGuruLabel">Evaluasi Proyek Kelompok</h5>
          </div>
          <div class="modal-body">
            <form id="formEvaluasiGuru" method="POST" class="p-3 border rounded bg-light">
                <input type="hidden" name="group_project" value="' . htmlspecialchars($group_project) . '">
                <input type="hidden" name="cmid" value="' . htmlspecialchars($cmid) . '">

                <div class="mb-3">
                    <label for="teacher_feedback" class="form-label fw-bold">Feedback / Evaluasi untuk Kelompok <span class="text-danger">*</span></label>
                    <textarea id="teacher_feedback" name="teacher_feedback" 
                        class="form-control" 
                        placeholder="Berikan evaluasi, saran, dan penilaian untuk proyek kelompok ini..." 
                        rows="6" required>' . htmlspecialchars($evaluation_info['teacher_feedback'] ?? '') . '</textarea>
                </div>
            </form>
          </div>
          <div class="modal-footer">
              <button id="btnSimpanEvaluasi" type="button" class="btn rounded-pill px-4" style="background: linear-gradient(135deg, #3b82f6, #6366f1); color:#ffffff">
                  <i class="fas fa-save"></i> Simpan Evaluasi
              </button>
              <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
          </div>
</div>
</div>
</div>
';

// ==================== STEP 8: REFLEKSI PEMBELAJARAN (TEACHER VIEW) ====================
// Decode reflection_data for Syntax 8
$reflection_info = [];
if (!empty($step->reflection_data)) {
    $reflection_info = json_decode($step->reflection_data, true);
}

echo '
<div class="container mx-auto p-3" id="dataStep8">
    <div class="row">
        <div class="col-12">' .
            ($step7_status == "Selesai" || $step7_status == "Mengerjakan" ?
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">8</span>
                        <i class="fas fa-lightbulb" style="color: var(--step8-color);"></i>
                        <span>Refleksi Pembelajaran</span>
                    </div>
                    <span class="status-badge ' . (!empty($reflection_info) ? 'filled"><i class="fas fa-check-circle"></i> Sudah Diisi' : 'empty"><i class="fas fa-clock"></i> Belum Diisi') . '</span>
                </div>
                <div class="card step-card step-8">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Refleksi Kelompok</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8fafc;">' .
                    (!empty($reflection_info) ?
                        '<div class="info-panel mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-question-circle me-1"></i>Pertanyaan 1</h6>
                            <p class="fw-bold mb-2">Apa pengalaman baru yang kalian dapatkan?</p>
                            <div class="bg-white p-3 rounded border">' . nl2br(htmlspecialchars($reflection_info['q1'] ?? '-')) . '</div>
                        </div>
                        <div class="info-panel mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-question-circle me-1"></i>Pertanyaan 2</h6>
                            <p class="fw-bold mb-2">Apa kendala yang dihadapi dan solusinya?</p>
                            <div class="bg-white p-3 rounded border">' . nl2br(htmlspecialchars($reflection_info['q2'] ?? '-')) . '</div>
                        </div>
                        <div class="info-panel mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-question-circle me-1"></i>Pertanyaan 3</h6>
                            <p class="fw-bold mb-2">Bagaimana kesan pembelajaran berbasis proyek ini?</p>
                            <div class="bg-white p-3 rounded border">' . nl2br(htmlspecialchars($reflection_info['q3'] ?? '-')) . '</div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            <i class="fas fa-clock me-1"></i> Dikirim: ' . htmlspecialchars($reflection_info['submitted_at'] ?? '-') . '
                        </p>' :
                        '<div class="alert alert-secondary premium-alert"><i class="fas fa-info-circle me-2"></i>Kelompok ini belum mengirimkan refleksi pembelajaran.</div>'
                    ) .
                    '</div>
                </div>' :
                '<div class="step-header">
                    <div class="step-title">
                        <span class="step-number">8</span>
                        <i class="fas fa-lightbulb" style="color: var(--step8-color);"></i>
                        <span>Refleksi Pembelajaran</span>
                    </div>
                    <span class="status-badge empty"><i class="fas fa-lock"></i> Terkunci</span>
                </div>
                <div class="alert alert-warning premium-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Kelompok ini belum menyelesaikan tahap 7 (Penilaian & Evaluasi).
                </div>'
            ) .
        '</div>
    </div>
</div>';

?>
