@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Active Events / Projects</h2>
            <p class="text-xs text-slate-500 mt-1">सबै कार्यक्रम / Project हरूको OT claim स्थिति यहाँ हेर्नुहोस्।</p>  
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('events.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-4 py-2.5 rounded-lg font-medium text-xs hover:bg-purple-700 transition shadow-sm">
                <i class="fas fa-calendar-plus"></i>
                <span>नयाँ Event दर्ता</span>
            </a>
            <a href="{{ route('overtime.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-slate-700 text-white px-4 py-2.5 rounded-lg font-medium text-xs hover:bg-slate-800 transition shadow-sm">
                <i class="fas fa-plus-circle"></i>
                <span>Log General OT (सामान्य प्रयोजन)</span>
            </a>
        </div>
    </div>

       {{-- Approved / Unapproved Tabs --}}
       <span>Event </span><break>
    <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg w-fit">
        
          <a href="{{ route('events.list', ['filter' => 'approved']) }}"
           class="px-4 py-2 rounded-md text-xs font-semibold transition {{ $filter === 'approved' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Approved <span class="ml-1 inline-flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-full w-5 h-5 text-[10px] font-bold">{{ $approvedCount }}</span>
        </a>
         <a href="{{ route('events.list', ['filter' => 'all']) }}"
           class="px-4 py-2 rounded-md text-xs font-semibold transition {{ $filter === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            सबै
        </a>
        <a href="{{ route('events.list', ['filter' => 'unapproved']) }}"
           class="px-4 py-2 rounded-md text-xs font-semibold transition {{ $filter === 'unapproved' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Unapproved <span class="ml-1 inline-flex items-center justify-center bg-amber-100 text-amber-700 rounded-full w-5 h-5 text-[10px] font-bold">{{ $unapprovedCount }}</span>
        </a>
      
       
    </div>

    {{-- Approved ट्याब भित्रको sub-tab: OT Approved vs Pending (event_approval_status Approved भएका मध्ये workflow_status अनुसार) --}}
    @if($filter === 'approved')
     <span>OT </span><break>
        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 p-1 rounded-lg w-fit">
            <a href="{{ route('events.list', ['filter' => 'approved']) }}"
               class="px-3 py-1.5 rounded-md text-[11px] font-semibold transition {{ !$wfFilter ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                सबै 
            </a>
            <a href="{{ route('events.list', ['filter' => 'approved', 'wf' => 'ot_approved']) }}"
               class="px-3 py-1.5 rounded-md text-[11px] font-semibold transition {{ $wfFilter === 'ot_approved' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                OT Approved <span class="ml-1 inline-flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-full w-5 h-5 text-[10px] font-bold">{{ $otApprovedCount }}</span>
            </a>
            <a href="{{ route('events.list', ['filter' => 'approved', 'wf' => 'pending']) }}"
               class="px-3 py-1.5 rounded-md text-[11px] font-semibold transition {{ $wfFilter === 'pending' ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Pending <span class="ml-1 inline-flex items-center justify-center bg-amber-100 text-amber-700 rounded-full w-5 h-5 text-[10px] font-bold">{{ $otPendingCount }}</span>
            </a>
        </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>  
        </div>
    @endif

    {{-- Card wrapper --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Event Name</th>  
                        <th class="p-4">Department</th>  
                        <th class="p-4">Date Range</th>  
                        <th class="p-4">Status</th>  
                        <th class="p-4">Event Approval</th>
                        <th class="p-4">Approval</th>
                        <th class="p-4 text-right">Action</th>  
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition {{ !$event->is_active ? 'bg-slate-50/60' : '' }}">  

                            {{-- Event name + OT badges --}}
                            <td class="p-4 align-top">
                                @if($event->can_view_details)
                                    <button type="button" onclick="openOtDetailsModal({{ $event->id }})"
                                            class="font-semibold text-blue-700 hover:text-blue-900 hover:underline text-left {{ !$event->is_active ? 'text-slate-400' : '' }}">
                                        {{ $event->event_name }}
                                    </button>
                                @else
                                    <div class="font-semibold text-slate-800 {{ !$event->is_active ? 'text-slate-400' : '' }}">
                                        {{ $event->event_name }}  
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @forelse($event->status_summary as $status => $count)
                                        @php
                                            $colors = match(strtolower($status)) {
                                                'pending'  => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'light' => 'bg-amber-50 border-amber-200'],
                                                'rejected' => ['bg' => 'bg-rose-500',  'text' => 'text-rose-700',  'light' => 'bg-rose-50 border-rose-200'],
                                                'verified' => ['bg' => 'bg-emerald-500','text' => 'text-emerald-700','light' => 'bg-emerald-50 border-emerald-200'],
                                                default    => ['bg' => 'bg-blue-500',  'text' => 'text-blue-700',  'light' => 'bg-blue-50 border-blue-200'],
                                            };
                                        @endphp
                                        <div class="flex items-center gap-1.5 {{ $colors['light'] }} border rounded-full pl-2.5 pr-1 py-0.5 shadow-2xs">
                                            <span class="text-[10px] font-bold {{ $colors['text'] }} uppercase tracking-wide">
                                                {{ $status }}  
                                            </span>
                                            <span class="{{ $colors['bg'] }} text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                                                {{ $count }}  
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-[10px] text-slate-400 italic">कुनै claim छैन</span>  
                                    @endforelse
                                </div>
                            </td>

                            <td class="p-4 align-top text-xs text-slate-600">{{ $event->department }}</td>  

                            <td class="p-4 align-top text-xs text-slate-600 whitespace-nowrap">
                                {{ adToBs($event->start_date) }}
                                <span class="text-slate-300 mx-1">→</span>
                                {{ adToBs($event->end_date) }}  
                            </td>

                            <td class="p-4 align-top">
                                @if($event->is_active)
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>  
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Disabled
                                    </span>  
                                @endif
                            </td>

                            {{-- Event-level Approval गेट (OT entry खुल्ने/नखुल्ने) — workflow_status भन्दा छुट्टै --}}
                            <td class="p-4 align-top">
                                @php
                                    $eaColors = match($event->event_approval_status) {
                                        'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default    => 'bg-amber-50 text-amber-700 border-amber-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $eaColors }}">
                                    {{ $event->event_approval_status ?? 'Pending' }}
                                </span>
                                @if($event->event_approval_status === 'Rejected' && $event->event_rejection_reason)
                                    <p class="text-[10px] text-rose-500 mt-1 max-w-[160px]">{{ $event->event_rejection_reason }}</p>
                                @endif
                            </td>

                            {{-- Approval Workflow स्थिति --}}
                            <td class="p-4 align-top">
                                @php
                                    $wfColors = match($event->workflow_status) {
                                        'Submitted'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Recommended' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Approved'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        default       => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $wfColors }}">
                                    {{ $event->workflow_status ?? 'Draft' }}
                                </span>
                                @if($event->workflow_status === 'Rejected' || $event->rejection_reason)
                                    <p class="text-[10px] text-rose-500 mt-1 max-w-[160px]">{{ $event->rejection_reason }}</p>
                                @endif
                            </td>

                            <td class="p-4 align-top text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    @if($event->is_active && $event->can_enter_ot)
                                        <a href="{{ route('overtime.create', ['event_id' => $event->id]) }}"
                                           class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-2xs">
                                            Entry Overtime
                                        </a>  
                                    @else
                                        <span class="bg-slate-100 text-slate-400 px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed border border-slate-200"
                                              title="{{ $event->event_approval_status === 'Rejected' ? 'यो कार्यक्रम Reject भएकोले OT Entry बन्द छ' : 'सिफारिस गर्नेले Event Approve नगरेसम्म OT Entry खुल्दैन' }}">
                                            Entry Overtime
                                        </span>  
                                    @endif

                                    {{-- Event Approval: सिफारिस गर्नेले Event बन्नासाथ Approve/Reject गर्ने (OT भर्नुअघि नै) --}}
                                    @if($event->can_approve_event_creation)
                                        <form action="{{ route('events.approveCreation', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('के तपाईं यो Event Approve गर्न चाहनुहुन्छ? Approve गरेपछि सबैले यसमा OT Entry गर्न मिल्नेछ।')">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition shadow-2xs">
                                                Event Approve
                                            </button>
                                        </form>
                                    @endif
                                    @if($event->can_reject_event_creation)
                                        <button type="button" onclick="document.getElementById('event-creation-reject-modal-{{ $event->id }}').style.display='flex'"
                                                class="bg-rose-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-rose-700 transition shadow-2xs">
                                            Event Reject
                                        </button>
                                        <div id="event-creation-reject-modal-{{ $event->id }}" style="display:none;" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
                                            <div class="bg-white rounded-xl max-w-sm w-full p-5 shadow-xl text-left border border-slate-100">
                                                <h3 class="text-sm font-bold text-slate-800 mb-2">❌ Event Reject गर्ने कारण लेख्नुहोस्</h3>
                                                <form action="{{ route('events.rejectCreation', $event->id) }}" method="POST">
                                                    @csrf
                                                    <textarea name="reason" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none mb-4" placeholder="अस्वीकृत गर्नुको कारण..." required></textarea>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" onclick="document.getElementById('event-creation-reject-modal-{{ $event->id }}').style.display='none'"
                                                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">रद्द</button>
                                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                    @if($event->can_resubmit_event_approval)
                                        <form action="{{ route('events.resubmitApproval', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('फेरि Event Approval को लागि पठाउने हो?')">
                                            @csrf
                                            <button type="submit" class="bg-sky-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-sky-700 transition shadow-2xs">
                                                फेरि पठाउनुहोस्
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('events.print', $event->id) }}" target="_blank"
                                       class="bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-purple-700 transition shadow-2xs">
                                        Print
                                    </a>  

                                    @if($event->isEditable())
                                        <a href="{{ route('events.edit', $event->id) }}"
                                           class="bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-purple-700 transition shadow-2xs">
                                            Edit
                                        </a>

                                        <form action="{{ route('events.toggle', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('के तपाईं यो कार्यक्रमको Status बदल्न चाहनुहुन्छ?')">  
                                            @csrf
                                            <button type="submit"
                                                class="{{ $event->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-2xs">
                                                {{ $event->is_active ? 'Disable' : 'Enable' }}
                                            </button>  
                                        </form>
                                    @endif

                                    {{-- Submit: Event बनाउने ब्यक्तिले सबैको OT भरिसकेपछि --}}
                                    @if($event->can_submit)
                                        <form action="{{ route('events.submit', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Submit गरेपछि यो कार्यक्रममा नयाँ OT थप्न वा edit गर्न रोकिन्छ। अगाडि बढ्ने हो?')">
                                            @csrf
                                            <button type="submit" class="bg-slate-800 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-slate-900 transition shadow-2xs">
                                                Submit
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Recommend: सिफारिस गर्ने ब्यक्तिले --}}
                                    @if($event->can_recommend)
                                        <form action="{{ route('events.recommend', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('के तपाईं यो कार्यक्रम सिफारिस गर्न चाहनुहुन्छ?')">
                                            @csrf
                                            <button type="submit" class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-2xs">
                                                सिफारिस (Recommend)
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Approve: स्वीकृति गर्ने ब्यक्तिले --}}
                                    @if($event->can_approve)
                                        <form action="{{ route('events.approve', $event->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('के तपाईं यो कार्यक्रम स्वीकृत गर्न चाहनुहुन्छ?')">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition shadow-2xs">
                                                स्वीकृत (Approve)
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Reject: Submitted भए Recommender, Recommended भए Approver --}}
                                    @if($event->can_reject)
                                        <button type="button" onclick="document.getElementById('event-reject-modal-{{ $event->id }}').style.display='flex'"
                                                class="bg-rose-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-rose-700 transition shadow-2xs">
                                            Reject
                                        </button>
                                        <div id="event-reject-modal-{{ $event->id }}" style="display:none;" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
                                            <div class="bg-white rounded-xl max-w-sm w-full p-5 shadow-xl text-left border border-slate-100">
                                                <h3 class="text-sm font-bold text-slate-800 mb-2">❌ Reject गर्ने कारण लेख्नुहोस्</h3>
                                                <form action="{{ route('events.reject', $event->id) }}" method="POST">
                                                    @csrf
                                                    <textarea name="reason" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none mb-4" placeholder="अस्वीकृत गर्नुको कारण..." required></textarea>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" onclick="document.getElementById('event-reject-modal-{{ $event->id }}').style.display='none'"
                                                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">रद्द</button>
                                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400">
                                <i class="fas fa-calendar-times text-2xl mb-2 block text-slate-300"></i>
                                अहिले कुनै पनि सक्रिय कार्यक्रम छैनन्।
                            </td>  
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- OT Detail Modal (Event name click गर्दा) -->
<div id="ot-details-modal" style="display:none;" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[85vh] overflow-hidden shadow-xl border border-slate-100 flex flex-col">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 id="ot-details-title" class="text-base font-bold text-slate-800"></h3>
                <p id="ot-details-meta" class="text-xs text-slate-500 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeOtDetailsModal()" class="text-slate-400 hover:text-slate-700 text-xl leading-none">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto">
            <div id="ot-details-loading" class="text-center text-slate-400 py-8">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <p class="text-xs mt-2">लोड हुँदैछ...</p>
            </div>
            <div id="ot-details-error" class="text-center text-rose-500 py-8 text-sm" style="display:none;"></div>
            <table id="ot-details-table" class="w-full text-left text-xs border-collapse" style="display:none;">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="p-2.5">कर्मचारी</th>
                        <th class="p-2.5">पद</th>
                        <th class="p-2.5">मिति</th>
                        <th class="p-2.5">समय</th>
                        <th class="p-2.5 text-center">घण्टा</th>
                        <th class="p-2.5 text-center">खाजा</th>
                        <th class="p-2.5 text-center">Status</th>
                        <th id="ot-details-action-th" class="p-2.5 text-center" style="display:none;">कार्य</th>
                    </tr>
                </thead>
                <tbody id="ot-details-tbody" class="divide-y divide-slate-100"></tbody>
                <tfoot>
                    <tr class="font-bold bg-slate-50">
                        <td colspan="4" class="p-2.5 text-right">जम्मा</td>
                        <td id="ot-details-total-hours" class="p-2.5 text-center"></td>
                        <td id="ot-details-total-tiffin" class="p-2.5 text-center"></td>
                        <td></td>
                        <td id="ot-details-action-td-empty" style="display:none;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
let currentOtDetailsEventId = null;

function openOtDetailsModal(eventId) {
    currentOtDetailsEventId = eventId;
    const modal = document.getElementById('ot-details-modal');
    const loading = document.getElementById('ot-details-loading');
    const errorBox = document.getElementById('ot-details-error');
    const table = document.getElementById('ot-details-table');
    const tbody = document.getElementById('ot-details-tbody');

    modal.style.display = 'flex';
    loading.style.display = 'block';
    errorBox.style.display = 'none';
    table.style.display = 'none';
    tbody.innerHTML = '';

    fetch(`/events/${eventId}/ot-details`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            loading.style.display = 'none';
            if (!ok) {
                errorBox.textContent = data.error || 'त्रुटि भयो।';
                errorBox.style.display = 'block';
                return;
            }

            document.getElementById('ot-details-title').textContent = data.event.name;
            document.getElementById('ot-details-meta').textContent =
                `बनाउने: ${data.event.creator} | सिफारिस: ${data.event.recommender} | स्वीकृति: ${data.event.approver} | स्थिति: ${data.event.workflow_status}`;

            const actionTh = document.getElementById('ot-details-action-th');
            const actionTdEmpty = document.getElementById('ot-details-action-td-empty');
            actionTh.style.display = data.can_manage_ot ? '' : 'none';
            actionTdEmpty.style.display = data.can_manage_ot ? '' : 'none';

            if (data.records.length === 0) {
                errorBox.textContent = 'यो कार्यक्रममा अहिलेसम्म कुनै OT रेकर्ड भरिएको छैन।';
                errorBox.style.display = 'block';
                return;
            }

            data.records.forEach(r => {
                const tr = document.createElement('tr');
                let actionCell = '';
                if (r.can_manage) {
                    actionCell = `
                        <td class="p-2.5 text-center whitespace-nowrap">
                            <a href="${r.edit_url}" class="inline-flex items-center px-2 py-1 rounded bg-blue-600 text-white text-[11px] font-medium hover:bg-blue-700 mr-1">Edit</a>
                            <button type="button" onclick="deleteOtFromModal(${r.id})" class="inline-flex items-center px-2 py-1 rounded bg-rose-600 text-white text-[11px] font-medium hover:bg-rose-700">Delete</button>
                        </td>`;
                } else if (data.can_manage_ot) {
                    actionCell = `<td class="p-2.5 text-center">-</td>`;
                }
                tr.innerHTML = `
                    <td class="p-2.5 font-medium text-slate-800">${r.employee_name} <span class="text-slate-400">(${r.employee_code})</span></td>
                    <td class="p-2.5 text-slate-600">${r.position}</td>
                    <td class="p-2.5 whitespace-nowrap">${r.ot_date}</td>
                    <td class="p-2.5 whitespace-nowrap">${r.from_time} - ${r.to_time}</td>
                    <td class="p-2.5 text-center">${r.total_hours}</td>
                    <td class="p-2.5 text-center">${r.tiffin_amount}</td>
                    <td class="p-2.5 text-center">${r.status}</td>
                    ${actionCell}
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('ot-details-total-hours').textContent = data.total_hours;
            document.getElementById('ot-details-total-tiffin').textContent = data.total_tiffin;
            table.style.display = 'table';
        })
        .catch(() => {
            loading.style.display = 'none';
            errorBox.textContent = 'Network त्रुटि भयो, फेरि प्रयास गर्नुहोस्।';
            errorBox.style.display = 'block';
        });
}

function deleteOtFromModal(recordId) {
    if (!confirm('के तपाईं पक्का यो OT रेकर्ड हटाउन चाहनुहुन्छ?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/overtime/${recordId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: (() => { const fd = new FormData(); fd.append('_method', 'DELETE'); return fd; })(),
    })
    .then(() => {
        // Modal भित्रै तालिका ताजा गर्ने (पूरा page reload नगरी)
        if (currentOtDetailsEventId) {
            openOtDetailsModal(currentOtDetailsEventId);
        }
    })
    .catch(() => alert('हटाउँदा त्रुटि भयो, फेरि प्रयास गर्नुहोस्।'));
}

function closeOtDetailsModal() {
    document.getElementById('ot-details-modal').style.display = 'none';
}
</script>
@endsection