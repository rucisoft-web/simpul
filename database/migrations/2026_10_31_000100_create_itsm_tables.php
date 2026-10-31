<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema Simpul ITSM.
 * Mengikuti data model iTop (itop-config-mgmt, itop-service-mgmt, ticketing ITIL,
 * change, problem, knowledge) dan diperluas dengan praktik ITIL 4
 * (Monitoring & Event Management, Continual Improvement).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Foundation: Organization, Location, Contact ----------
        Schema::create('organizations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('organizations')->nullOnDelete();
            $t->string('code', 20)->unique();
            $t->string('name');
            $t->string('industry')->nullable();
            $t->string('color', 20)->default('#2563eb');
            $t->string('website')->nullable();
            $t->string('status')->default('active');
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->string('name');
            $t->string('type')->default('office'); // hq, region, office, pool, site, datacenter, campus, building
            $t->string('address')->nullable();
            $t->string('city')->nullable();
            $t->string('country')->default('Indonesia');
            $t->decimal('lat', 10, 6)->nullable();
            $t->decimal('lng', 10, 6)->nullable();
            $t->timestamps();
        });

        Schema::create('persons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('manager_id')->nullable()->constrained('persons')->nullOnDelete();
            $t->string('first_name');
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('function')->nullable();
            $t->string('employee_number')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('teams', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('function')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
        });

        Schema::create('person_team', function (Blueprint $t) {
            $t->id();
            $t->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $t->foreignId('team_id')->constrained()->cascadeOnDelete();
            $t->string('role')->nullable();
        });

        // ---------- Typology ----------
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('country')->nullable();
            $t->string('color', 20)->default('#64748b');
            $t->timestamps();
        });

        Schema::create('models', function (Blueprint $t) {
            $t->id();
            $t->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type'); // class CI yang cocok: NetworkDevice, Server, PC, ...
            $t->timestamps();
        });

        // ---------- CMDB: FunctionalCI (single table, kolom class) ----------
        Schema::create('cis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->string('class', 40)->index();       // NetworkDevice, Server, PC, Rack, ApplicationSolution, ...
            $t->string('name');
            $t->string('status', 20)->default('production'); // implementation, production, stock, obsolete
            $t->string('business_criticity', 10)->default('medium'); // high, medium, low
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('model_id')->nullable()->constrained('models')->nullOnDelete();
            $t->string('network_role', 30)->nullable(); // wan, firewall, core, distribution, access, wireless, endpoint
            $t->foreignId('parent_id')->nullable()->constrained('cis')->nullOnDelete(); // uplink (topologi)
            $t->foreignId('rack_id')->nullable()->constrained('cis')->nullOnDelete();
            $t->foreignId('team_id')->nullable()->constrained()->nullOnDelete(); // tim pendukung
            $t->string('serial_number')->nullable();
            $t->string('asset_number')->nullable();
            $t->string('management_ip', 45)->nullable();
            $t->string('os_family')->nullable();
            $t->string('os_version')->nullable();
            $t->string('firmware')->nullable();
            $t->string('cpu')->nullable();
            $t->string('ram')->nullable();
            $t->unsignedSmallInteger('rack_units')->nullable();
            $t->date('purchase_date')->nullable();
            $t->date('warranty_end')->nullable();
            $t->date('move2production')->nullable();
            $t->text('description')->nullable();
            $t->json('extra')->nullable();
            $t->timestamps();
        });

        Schema::create('physical_interfaces', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ci_id')->constrained('cis')->cascadeOnDelete();
            $t->string('name');
            $t->string('ip_address', 45)->nullable();
            $t->string('mac_address', 20)->nullable();
            $t->string('speed')->nullable();
            $t->string('vlan')->nullable();
        });

        // Relasi antar CI (runs_on, depends_on, connected_to, member_of) untuk analisis dampak
        Schema::create('ci_relations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('source_id')->constrained('cis')->cascadeOnDelete(); // bergantung pada...
            $t->foreignId('target_id')->constrained('cis')->cascadeOnDelete();
            $t->string('type', 30)->default('depends_on');
        });

        Schema::create('ci_person', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ci_id')->constrained('cis')->cascadeOnDelete();
            $t->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $t->string('role')->nullable();
        });

        // ---------- Service Management ----------
        Schema::create('service_families', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('icon')->nullable();
            $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete(); // provider
            $t->foreignId('service_family_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('status')->default('production');
            $t->string('value_stream')->nullable(); // ITIL 4
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('service_subcategories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('request_type', 20)->default('incident'); // incident, service_request
            $t->string('status')->default('production');
        });

        Schema::create('ci_service', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ci_id')->constrained('cis')->cascadeOnDelete();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('slas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('slts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sla_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedTinyInteger('priority'); // 1 critical .. 4 low
            $t->string('request_type', 20);
            $t->string('metric', 5); // tto, ttr
            $t->unsignedInteger('value');
            $t->string('unit', 10)->default('hours');
        });

        Schema::create('contracts', function (Blueprint $t) {
            $t->id();
            $t->string('kind', 20)->default('customer'); // customer, provider
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete(); // customer
            $t->foreignId('provider_id')->nullable()->constrained('organizations')->nullOnDelete();
            $t->string('vendor')->nullable(); // untuk provider contract (vendor eksternal)
            $t->foreignId('sla_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('contract_type')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->decimal('cost', 15, 2)->nullable();
            $t->string('currency', 5)->default('IDR');
            $t->string('billing_frequency')->nullable();
            $t->string('status')->default('production');
            $t->timestamps();
        });

        Schema::create('contract_service', function (Blueprint $t) {
            $t->id();
            $t->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('ci_contract', function (Blueprint $t) {
            $t->id();
            $t->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ci_id')->constrained('cis')->cascadeOnDelete();
        });

        // ---------- Ticketing (Incident, ServiceRequest, Problem, Change) ----------
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->string('ref', 20)->unique();
            $t->string('type', 20)->index(); // incident, request, problem, change
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('caller_id')->nullable()->constrained('persons')->nullOnDelete();
            $t->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('agent_id')->nullable()->constrained('persons')->nullOnDelete();
            $t->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('service_subcategory_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('tickets')->nullOnDelete(); // problem/change induk
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('status', 30)->index();
            $t->unsignedTinyInteger('impact')->default(3);   // 1 departemen, 2 layanan, 3 individu
            $t->unsignedTinyInteger('urgency')->default(3);  // 1 critical .. 4 low
            $t->unsignedTinyInteger('priority')->default(4); // 1 critical .. 4 low
            $t->string('origin', 20)->default('portal');     // portal, email, phone, monitoring, walk-in
            // Change
            $t->string('change_type', 20)->nullable(); // standard (routine), normal, emergency
            $t->string('risk', 10)->nullable();
            $t->boolean('outage')->default(false);
            $t->text('fallback_plan')->nullable();
            $t->dateTime('planned_start')->nullable();
            $t->dateTime('planned_end')->nullable();
            // Problem
            $t->text('root_cause')->nullable();
            $t->text('workaround')->nullable();
            // Resolution & SLA
            $t->string('resolution_code', 30)->nullable();
            $t->text('solution')->nullable();
            $t->dateTime('tto_deadline')->nullable();
            $t->dateTime('ttr_deadline')->nullable();
            $t->dateTime('assigned_at')->nullable();
            $t->dateTime('resolved_at')->nullable();
            $t->dateTime('closed_at')->nullable();
            $t->boolean('sla_tto_passed')->default(false);
            $t->boolean('sla_ttr_passed')->default(false);
            $t->unsignedTinyInteger('satisfaction')->nullable();
            $t->timestamps();
            $t->index(['organization_id', 'type', 'status']);
        });

        Schema::create('ci_ticket', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ci_id')->constrained('cis')->cascadeOnDelete();
            $t->string('impact_code', 20)->default('manual'); // manual, computed
        });

        Schema::create('ticket_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->string('author');
            $t->string('kind', 10)->default('public'); // public, private, system
            $t->text('message');
            $t->timestamp('created_at')->nullable();
        });

        // ---------- Knowledge ----------
        Schema::create('known_errors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('problem_id')->nullable()->constrained('tickets')->nullOnDelete();
            $t->string('name');
            $t->string('domain', 20); // Application, Desktop, Network, Server
            $t->text('symptom');
            $t->string('error_code')->nullable();
            $t->string('vendor')->nullable();
            $t->string('model')->nullable();
            $t->text('root_cause')->nullable();
            $t->text('workaround')->nullable();
            $t->text('solution')->nullable();
            $t->timestamps();
        });

        Schema::create('faq_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('faq_category_id')->constrained()->cascadeOnDelete();
            $t->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('keywords')->nullable();
            $t->text('summary');
            $t->text('description');
            $t->unsignedInteger('views')->default(0);
            $t->timestamps();
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('kind', 10)->default('note'); // note, web, file
            $t->string('url')->nullable();
            $t->text('content')->nullable();
            $t->string('status')->default('published');
            $t->timestamps();
        });

        // ---------- ITIL 4: Monitoring & Event Management ----------
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ci_id')->nullable()->constrained('cis')->nullOnDelete();
            $t->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $t->string('source', 30); // Zabbix, PRTG, SolarWinds, Wazuh ...
            $t->string('severity', 15); // info, warning, minor, major, critical
            $t->string('message');
            $t->string('status', 15)->default('open'); // open, acknowledged, closed
            $t->timestamp('occurred_at');
        });

        // ---------- ITIL 4: Continual Improvement Register ----------
        Schema::create('improvements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_id')->nullable()->constrained('persons')->nullOnDelete();
            $t->string('title');
            $t->string('practice'); // praktik ITIL 4 terkait
            $t->string('status', 20)->default('proposed'); // proposed, approved, in_progress, done
            $t->string('guiding_principle')->nullable();
            $t->text('description')->nullable();
            $t->string('benefit')->nullable();
            $t->unsignedTinyInteger('progress')->default(0);
            $t->date('target_date')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['improvements', 'events', 'documents', 'faqs', 'faq_categories', 'known_errors', 'ticket_logs', 'ci_ticket', 'tickets',
            'ci_contract', 'contract_service', 'contracts', 'slts', 'slas', 'ci_service', 'service_subcategories', 'services', 'service_families',
            'ci_person', 'ci_relations', 'physical_interfaces', 'cis', 'models', 'brands', 'person_team', 'teams', 'persons', 'locations', 'organizations'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
