<?php

namespace App\Support;

/**
 * Aturan ITIL 4: tipe tiket, siklus status (lifecycle iTop), matriks prioritas, dan praktik.
 */
class Itil
{
    public const CLOSED_STATUSES = ['resolved', 'closed', 'rejected', 'not_approved'];

    public const TYPES = [
        'incident' => ['label' => 'Insiden', 'prefix' => 'I', 'practice' => 'Incident Management', 'color' => 'rose'],
        'request' => ['label' => 'Permintaan Layanan', 'prefix' => 'R', 'practice' => 'Service Request Management', 'color' => 'sky'],
        'problem' => ['label' => 'Problem', 'prefix' => 'P', 'practice' => 'Problem Management', 'color' => 'amber'],
        'change' => ['label' => 'Perubahan', 'prefix' => 'C', 'practice' => 'Change Enablement', 'color' => 'violet'],
    ];

    public const STATUS_LABELS = [
        'new' => 'Baru', 'assigned' => 'Ditugaskan', 'pending' => 'Tertunda', 'escalated_tto' => 'Eskalasi TTO',
        'escalated_ttr' => 'Eskalasi TTR', 'waiting_approval' => 'Menunggu Persetujuan', 'approved' => 'Disetujui',
        'rejected' => 'Ditolak', 'resolved' => 'Selesai', 'closed' => 'Ditutup', 'validated' => 'Tervalidasi',
        'not_approved' => 'Tidak Disetujui', 'planned' => 'Terjadwal', 'implemented' => 'Diimplementasi',
        'monitored' => 'Dipantau', 'known_error' => 'Known Error',
    ];

    public const STATUS_COLORS = [
        'new' => 'bg-sky-100 text-sky-700', 'assigned' => 'bg-indigo-100 text-indigo-700',
        'pending' => 'bg-amber-100 text-amber-700', 'escalated_tto' => 'bg-rose-100 text-rose-700',
        'escalated_ttr' => 'bg-rose-100 text-rose-700', 'waiting_approval' => 'bg-amber-100 text-amber-700',
        'approved' => 'bg-teal-100 text-teal-700', 'rejected' => 'bg-slate-200 text-slate-600',
        'resolved' => 'bg-emerald-100 text-emerald-700', 'closed' => 'bg-slate-100 text-slate-600',
        'validated' => 'bg-cyan-100 text-cyan-700', 'not_approved' => 'bg-slate-200 text-slate-600',
        'planned' => 'bg-violet-100 text-violet-700', 'implemented' => 'bg-fuchsia-100 text-fuchsia-700',
        'monitored' => 'bg-teal-100 text-teal-700', 'known_error' => 'bg-orange-100 text-orange-700',
    ];

    /** Transisi status per tipe tiket: status => [aksi => status tujuan]. */
    public const TRANSITIONS = [
        'incident' => [
            'new' => ['assign' => 'assigned'],
            'assigned' => ['pending' => 'pending', 'resolve' => 'resolved', 'escalate' => 'escalated_ttr'],
            'escalated_tto' => ['assign' => 'assigned'],
            'escalated_ttr' => ['pending' => 'pending', 'resolve' => 'resolved'],
            'pending' => ['assign' => 'assigned', 'resolve' => 'resolved'],
            'resolved' => ['close' => 'closed', 'reopen' => 'assigned'],
        ],
        'request' => [
            'new' => ['submit_approval' => 'waiting_approval', 'assign' => 'assigned'],
            'waiting_approval' => ['approve' => 'approved', 'reject' => 'rejected'],
            'approved' => ['assign' => 'assigned'],
            'assigned' => ['pending' => 'pending', 'resolve' => 'resolved'],
            'pending' => ['assign' => 'assigned', 'resolve' => 'resolved'],
            'resolved' => ['close' => 'closed', 'reopen' => 'assigned'],
        ],
        'problem' => [
            'new' => ['assign' => 'assigned'],
            'assigned' => ['known_error' => 'known_error', 'resolve' => 'resolved'],
            'known_error' => ['resolve' => 'resolved'],
            'resolved' => ['close' => 'closed', 'reopen' => 'assigned'],
        ],
        'change' => [
            'new' => ['validate' => 'validated', 'reject' => 'rejected', 'plan' => 'planned'],
            'validated' => ['submit_approval' => 'waiting_approval'],
            'waiting_approval' => ['approve' => 'approved', 'not_approve' => 'not_approved'],
            'approved' => ['plan' => 'planned'],
            'planned' => ['implement' => 'implemented'],
            'implemented' => ['monitor' => 'monitored', 'close' => 'closed'],
            'monitored' => ['close' => 'closed'],
        ],
    ];

    public const ACTION_LABELS = [
        'assign' => 'Tugaskan', 'pending' => 'Tunda', 'resolve' => 'Selesaikan', 'escalate' => 'Eskalasi',
        'close' => 'Tutup', 'reopen' => 'Buka Kembali', 'submit_approval' => 'Ajukan Persetujuan',
        'approve' => 'Setujui', 'reject' => 'Tolak', 'known_error' => 'Tandai Known Error',
        'validate' => 'Validasi', 'not_approve' => 'Tidak Disetujui (CAB)', 'plan' => 'Jadwalkan',
        'implement' => 'Implementasi', 'monitor' => 'Pantau (PIR)',
    ];

    public const PRIORITIES = [
        1 => ['label' => 'P1 Kritis', 'class' => 'bg-rose-600 text-white'],
        2 => ['label' => 'P2 Tinggi', 'class' => 'bg-orange-500 text-white'],
        3 => ['label' => 'P3 Sedang', 'class' => 'bg-amber-300 text-amber-900'],
        4 => ['label' => 'P4 Rendah', 'class' => 'bg-slate-200 text-slate-700'],
    ];

    public const IMPACTS = [1 => 'Departemen / banyak layanan', 2 => 'Satu layanan', 3 => 'Individu'];
    public const URGENCIES = [1 => 'Kritis', 2 => 'Tinggi', 3 => 'Sedang', 4 => 'Rendah'];

    /** Matriks prioritas ITIL (impact x urgency). */
    public static function priority(int $impact, int $urgency): int
    {
        $matrix = [
            1 => [1 => 1, 2 => 1, 3 => 2, 4 => 3],
            2 => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
            3 => [1 => 2, 2 => 3, 3 => 4, 4 => 4],
        ];

        return $matrix[$impact][$urgency] ?? 4;
    }

    /** Target SLA default (jam) bila kontrak tidak mendefinisikan SLT. */
    public const DEFAULT_SLT = [
        1 => ['tto' => 0.25, 'ttr' => 4],
        2 => ['tto' => 0.5, 'ttr' => 8],
        3 => ['tto' => 2, 'ttr' => 24],
        4 => ['tto' => 4, 'ttr' => 72],
    ];

    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    public static function statusColor(string $status): string
    {
        return self::STATUS_COLORS[$status] ?? 'bg-slate-100 text-slate-700';
    }

    public static function transitions(string $type, string $status): array
    {
        return self::TRANSITIONS[$type][$status] ?? [];
    }

    /** 34 praktik ITIL 4, ditandai yang didukung aplikasi beserta modulnya. */
    public const PRACTICES = [
        'Praktik Manajemen Umum' => [
            ['Continual Improvement', 'improvements.index'], ['Information Security Management', 'events.index'],
            ['Knowledge Management', 'knowledge.index'], ['Measurement & Reporting', 'reports.index'],
            ['Organizational Change Management', null], ['Portfolio Management', 'services.index'],
            ['Project Management', null], ['Relationship Management', 'contacts.index'],
            ['Risk Management', 'changes.calendar'], ['Service Financial Management', 'contracts.index'],
            ['Strategy Management', null], ['Supplier Management', 'contracts.index'],
            ['Workforce & Talent Management', 'contacts.index'], ['Architecture Management', 'topology.index'],
        ],
        'Praktik Manajemen Layanan' => [
            ['Availability Management', 'reports.index'], ['Business Analysis', null],
            ['Capacity & Performance Management', 'events.index'], ['Change Enablement', 'tickets.index:change'],
            ['Incident Management', 'tickets.index:incident'], ['IT Asset Management', 'cis.index'],
            ['Monitoring & Event Management', 'events.index'], ['Problem Management', 'tickets.index:problem'],
            ['Release Management', 'changes.calendar'], ['Service Catalogue Management', 'services.index'],
            ['Service Configuration Management', 'cis.index'], ['Service Continuity Management', 'topology.index'],
            ['Service Design', 'services.index'], ['Service Desk', 'tickets.index'],
            ['Service Level Management', 'slas.index'], ['Service Request Management', 'tickets.index:request'],
            ['Service Validation & Testing', null],
        ],
        'Praktik Manajemen Teknis' => [
            ['Deployment Management', 'changes.calendar'], ['Infrastructure & Platform Management', 'topology.index'],
            ['Software Development & Management', null],
        ],
    ];

    public const GUIDING_PRINCIPLES = [
        'Focus on value', 'Start where you are', 'Progress iteratively with feedback',
        'Collaborate and promote visibility', 'Think and work holistically', 'Keep it simple and practical',
        'Optimize and automate',
    ];
}
