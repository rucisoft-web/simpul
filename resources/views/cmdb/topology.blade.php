@extends('layouts.app')
@section('title', 'Topologi Jaringan')
@section('header')
    <h1 class="h1">Topologi Jaringan Bertingkat</h1>
    <p class="sub">Pohon uplink: Internet → WAN Edge → Firewall → Core → Distribusi/ToR → Akses → AP & Endpoint. Klik node untuk buka/tutup cabang, klik dua kali untuk detail CI.</p>
@endsection

@section('content')
<div x-data="topo()" x-init="load()">
    <div class="card p-3 mb-4 flex flex-wrap items-center gap-2">
        @foreach ($orgs as $o)
            <a href="{{ route('topology.index', ['org' => $o->id]) }}" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $o->id === $org->id ? 'text-white border-transparent' : 'bg-white border-slate-200 hover:bg-slate-50' }}"
               style="{{ $o->id === $org->id ? 'background: '.$o->color : '' }}">{{ $o->name }}</a>
        @endforeach
        <div class="flex-1"></div>
        <div class="flex rounded-xl border border-slate-200 p-0.5 text-sm">
            <button @click="mode='network'; load()" class="px-3 py-1.5 rounded-lg font-semibold" :class="mode==='network' ? 'bg-slate-900 text-white' : ''">Jaringan</button>
            <button @click="mode='location'; load()" class="px-3 py-1.5 rounded-lg font-semibold" :class="mode==='location' ? 'bg-slate-900 text-white' : ''">Lokasi</button>
        </div>
        <label class="text-sm flex items-center gap-1.5" x-show="mode==='network'"><input type="checkbox" x-model="endpoints" @change="load()"> Tampilkan endpoint</label>
        <button class="btn btn-ghost" @click="expandAll()">Buka semua</button>
        <button class="btn btn-ghost" @click="collapse()">Ringkas</button>
        <button class="btn btn-ghost" @click="fit()">Pas layar</button>
    </div>

    <div class="grid lg:grid-cols-[1fr_260px] gap-4">
        <div class="card relative overflow-hidden" style="height: 72vh">
            <svg id="topo" class="w-full h-full bg-[radial-gradient(circle,#e2e8f0_1px,transparent_1px)] [background-size:18px_18px]"></svg>
            <div x-show="loading" class="absolute inset-0 grid place-items-center bg-white/60 text-sm text-slate-500">Memuat topologi…</div>
            <div x-show="sel" x-cloak class="absolute top-3 left-3 card p-4 w-72 text-sm shadow-lg">
                <div class="flex items-start gap-2"><p class="font-bold flex-1 break-all" x-text="sel?.name"></p><button @click="sel=null"><x-icon name="x" class="w-4 h-4"/></button></div>
                <p class="text-xs text-slate-500" x-text="sel?.sub"></p>
                <dl class="mt-2 space-y-1 text-xs">
                    <div class="flex justify-between" x-show="sel?.role"><dt class="text-slate-500">Peran</dt><dd class="font-semibold" x-text="sel?.role"></dd></div>
                    <div class="flex justify-between" x-show="sel?.ip"><dt class="text-slate-500">IP</dt><dd class="font-mono" x-text="sel?.ip"></dd></div>
                    <div class="flex justify-between" x-show="sel?.loc"><dt class="text-slate-500">Lokasi</dt><dd class="text-right" x-text="sel?.loc"></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Turunan</dt><dd x-text="sel?.count"></dd></div>
                </dl>
                <a x-show="sel?.url" :href="sel?.url" class="btn btn-primary w-full justify-center mt-3">Buka detail</a>
            </div>
        </div>
        <div class="space-y-4">
            <div class="card p-4">
                <h2 class="font-bold mb-2 text-sm">Legenda peran</h2>
                <template x-for="[k, v] in Object.entries(roles)"><div class="flex items-center gap-2 text-sm py-0.5"><span class="w-3 h-3 rounded-full" :style="'background:' + v.c"></span><span class="flex-1" x-text="v.l"></span><span class="text-xs text-slate-400" x-text="counts[k] || ''"></span></div></template>
                <p class="text-[11px] text-slate-400 mt-2">Garis tepi node = warna merek perangkat.</p>
            </div>
            <div class="card p-4 text-sm text-slate-600">
                <h2 class="font-bold mb-1 text-slate-800">Tips</h2>
                <ul class="list-disc ml-4 space-y-1 text-xs"><li>Scroll untuk zoom, drag untuk geser.</li><li>Klik node: buka/tutup cabang & lihat ringkasan.</li><li>Klik dua kali: buka halaman CI.</li><li>Mode Lokasi menampilkan pohon HQ → wilayah → site/gedung.</li></ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>
function topo() {
    const roles = {
        org: { l: 'Organisasi / Internet', c: '#0b1a44' }, wan: { l: 'WAN / Router', c: '#2563eb' }, firewall: { l: 'Firewall', c: '#dc2626' },
        core: { l: 'Core switch', c: '#7c3aed' }, distribution: { l: 'Distribusi / ToR', c: '#0891b2' }, access: { l: 'Access switch', c: '#059669' },
        wireless: { l: 'Access point', c: '#f59e0b' }, endpoint: { l: 'Endpoint / Server', c: '#64748b' },
        hq: { l: 'Kantor pusat / Kampus', c: '#0b1a44' }, campus: { l: 'Kampus', c: '#0b1a44' }, region: { l: 'Wilayah', c: '#2563eb' }, office: { l: 'Kantor', c: '#0891b2' },
        building: { l: 'Gedung', c: '#0891b2' }, pool: { l: 'Pool', c: '#059669' }, site: { l: 'Site lapangan', c: '#059669' }, datacenter: { l: 'Data center', c: '#7c3aed' },
    };
    let svg, g, zoom, root, i = 0;
    return {
        mode: 'network', endpoints: false, loading: false, sel: null, roles: Object.fromEntries(Object.entries(roles).slice(0, 8)), counts: @json($roleCounts),
        async load() {
            this.loading = true; this.sel = null;
            const res = await fetch(`{{ route('topology.data') }}?org={{ $org->id }}&mode=${this.mode}&endpoints=${this.endpoints ? 1 : 0}`);
            const data = await res.json();
            this.loading = false;
            this.roles = this.mode === 'network' ? Object.fromEntries(Object.entries(roles).slice(0, 8)) : Object.fromEntries(Object.entries(roles).slice(8));
            this.draw(data);
        },
        draw(data) {
            svg = d3.select('#topo'); svg.selectAll('*').remove();
            g = svg.append('g');
            zoom = d3.zoom().scaleExtent([0.15, 2.5]).on('zoom', e => g.attr('transform', e.transform));
            svg.call(zoom).on('dblclick.zoom', null);
            root = d3.hierarchy(data);
            root.descendants().forEach(d => { d._children = d.children; d.count = d.descendants().length - 1; if (d.depth >= (this.mode === 'network' ? 5 : 2)) d.children = null; });
            this.update(root, 0); requestAnimationFrame(() => this.fit());
        },
        update(src, dur = 300) {
            const self = this;
            const tree = d3.tree().nodeSize([34, 250]);
            tree(root);
            const nodes = root.descendants(), links = root.links();
            const link = g.selectAll('path.link').data(links, d => d.target.id || (d.target.id = ++i));
            link.enter().insert('path', 'g').attr('class', 'link').attr('fill', 'none').attr('stroke', '#cbd5e1').attr('stroke-width', 1.5)
                .merge(link).transition().duration(dur).attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));
            link.exit().remove();
            const node = g.selectAll('g.node').data(nodes, d => d.id || (d.id = ++i));
            const en = node.enter().append('g').attr('class', 'node').style('cursor', 'pointer')
                .on('click', (e, d) => { d.children = d.children ? null : d._children; self.sel = { ...d.data, count: d.count }; self.update(d); })
                .on('dblclick', (e, d) => { if (d.data.url) location.href = d.data.url; });
            en.append('circle').attr('r', 9);
            en.append('text').attr('class', 'nm').attr('dy', -2).attr('x', 14).style('font-size', '11px').style('font-weight', 600).attr('fill', '#0f172a');
            en.append('text').attr('class', 'sb').attr('dy', 11).attr('x', 14).style('font-size', '9.5px').attr('fill', '#64748b');
            const all = en.merge(node);
            all.transition().duration(dur).attr('transform', d => `translate(${d.y},${d.x})`);
            all.select('circle').attr('fill', d => (roles[d.data.role] || roles.endpoint).c).attr('stroke', d => d.data.color || '#fff').attr('stroke-width', 3)
                .attr('fill-opacity', d => d._children && !d.children ? 1 : .85);
            all.select('text.nm').text(d => d.data.name + (d._children && !d.children ? `  (+${d.count})` : ''));
            all.select('text.sb').text(d => [d.data.sub, d.data.ip].filter(Boolean).join(' · '));
            node.exit().remove();
        },
        expandAll() { root.descendants().forEach(d => d.children = d._children); this.update(root); setTimeout(() => this.fit(), 350); },
        collapse() { root.descendants().forEach(d => { d.children = d.depth >= 2 ? null : d._children; }); this.update(root); setTimeout(() => this.fit(), 350); },
        fit() {
            const b = g.node().getBBox(), el = document.getElementById('topo'), w = el.clientWidth, h = el.clientHeight;
            const s = Math.min(1.2, 0.9 / Math.max(b.width / w, b.height / h));
            svg.transition().duration(400).call(zoom.transform, d3.zoomIdentity.translate(w / 2 - s * (b.x + b.width / 2), h / 2 - s * (b.y + b.height / 2)).scale(s));
        },
    };
}
</script>
@endpush
