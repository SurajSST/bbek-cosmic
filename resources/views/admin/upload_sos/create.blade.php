@extends('layouts.app')

@section('header', 'Upload SO')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    soFromPreset: 'Cosmic',
    soFromCustom: 'Cosmic',
    billedToPreset: 'Prativa',
    billedToCustom: 'Prativa',
    
    // File & Preview state
    imagePreviewUrl: null,
    fileName: '',
    fileSize: '',

    // Camera state
    showCameraModal: false,
    mediaStream: null,
    cameraError: null,
    fitMode: 'contain',
    isMirrored: false,

    // File change handler
    onFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.setFilePreview(file);
        }
    },

    setFilePreview(file) {
        this.fileName = file.name;
        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
        const reader = new FileReader();
        reader.onload = (e) => {
            this.imagePreviewUrl = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    clearImage() {
        this.imagePreviewUrl = null;
        this.fileName = '';
        this.fileSize = '';
        const input = document.getElementById('so_image');
        if (input) input.value = '';
    },

    // Camera functionality
    async openCamera() {
        this.cameraError = null;
        this.showCameraModal = true;
        this.$nextTick(async () => {
            try {
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    }
                };
                this.mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
                const videoEl = this.$refs.cameraVideo;
                if (videoEl) {
                    videoEl.srcObject = this.mediaStream;
                    await videoEl.play();
                }
            } catch (err) {
                console.error('Camera access error:', err);
                try {
                    this.mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    const videoEl = this.$refs.cameraVideo;
                    if (videoEl) {
                        videoEl.srcObject = this.mediaStream;
                        await videoEl.play();
                    }
                } catch (fallbackErr) {
                    this.cameraError = 'Unable to access camera. Please allow camera permissions in your browser or select a file directly.';
                }
            }
        });
    },

    closeCamera() {
        if (this.mediaStream) {
            this.mediaStream.getTracks().forEach(track => track.stop());
            this.mediaStream = null;
        }
        this.showCameraModal = false;
    },

    capturePhoto() {
        const videoEl = this.$refs.cameraVideo;
        if (!videoEl || videoEl.videoWidth === 0) return;

        const canvas = document.createElement('canvas');
        canvas.width = videoEl.videoWidth;
        canvas.height = videoEl.videoHeight;
        const ctx = canvas.getContext('2d');

        if (this.isMirrored) {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
        }

        ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) return;
            const filename = 'so_camera_' + Date.now() + '.jpg';
            const file = new File([blob], filename, { type: 'image/jpeg' });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            const input = document.getElementById('so_image');
            if (input) {
                input.files = dataTransfer.files;
            }

            this.setFilePreview(file);
            this.closeCamera();
        }, 'image/jpeg', 0.92);
    }
}">

    <!-- Page Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Upload Sales Order
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Record a new Sales Order by capturing or attaching the SO image.</p>
        </div>
        <a href="{{ route('admin.upload-sos.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
            ← Back to Upload SOs
        </a>
    </div>

    <!-- Main Card Container -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <p class="font-bold">Please correct the validation errors below:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.upload-sos.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Section 1: Sales Order Reference Details -->
            <div class="space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Sales Order Information & Parties
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                    <!-- SO Number -->
                    <div>
                        <label for="so_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            SO Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="so_number" name="so_number" value="{{ old('so_number', 'SO-') }}" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- SO From -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            SO From <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="soFromPreset" @change="if (soFromPreset !== 'Other') soFromCustom = soFromPreset; else soFromCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="Cosmic">Cosmic</option>
                            <option value="Cloud">Cloud</option>
                            <option value="Dragon">Dragon</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="so_from" x-model="soFromCustom" required placeholder="Enter SO source..."
                            :readonly="soFromPreset !== 'Other'"
                            :class="soFromPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Billed To -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            To <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="billedToPreset" @change="if (billedToPreset !== 'Other') billedToCustom = billedToPreset; else billedToCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="Prativa">Prativa</option>
                            <option value="PBS">PBS</option>
                            <option value="Prativa Plus Two">Prativa Plus Two</option>
                            <option value="Prativa School">Prativa School</option>
                            <option value="EGA">EGA</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="billed_to" x-model="billedToCustom" required placeholder="Enter customer name..."
                            :readonly="billedToPreset !== 'Other'"
                            :class="billedToPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="billed" {{ old('status') === 'billed' ? 'selected' : '' }}>Billed</option>
                            <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: SO Image Upload & Camera Integration -->
            <div class="space-y-4 pt-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Upload Sales Order Image <span class="text-rose-500">*</span>
                    </span>
                    <span class="text-[11px] font-normal text-slate-400">Attach file or snap photo using camera</span>
                </h3>

                <div class="p-6 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 text-center space-y-4">
                    
                    <input type="file" id="so_image" name="so_image" accept="image/*" class="hidden" @change="onFileSelect($event)">
                    <input type="file" id="mobile_camera_input" accept="image/*" capture="environment" class="hidden" @change="onFileSelect($event)">

                    <!-- Image Preview View -->
                    <template x-if="imagePreviewUrl">
                        <div class="space-y-3">
                            <div class="relative max-w-md mx-auto rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-md bg-slate-950/40 group">
                                <img :src="imagePreviewUrl" alt="SO Preview" class="w-full max-h-72 object-contain mx-auto py-2">
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-3">
                                    <button type="button" @click="openCamera()" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500 shadow">
                                        📷 Retake with Camera
                                    </button>
                                    <button type="button" @click="document.getElementById('so_image').click()" class="px-3 py-1.5 rounded-xl bg-white text-slate-800 text-xs font-semibold hover:bg-slate-100 shadow">
                                        📁 Choose File
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-3 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                <span x-text="fileName" class="font-semibold text-slate-800 dark:text-slate-200"></span>
                                <span>•</span>
                                <span x-text="fileSize"></span>
                                <button type="button" @click="clearImage()" class="text-rose-500 hover:text-rose-700 font-sans text-xs underline font-semibold ml-2">
                                    Remove Photo
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Initial Dropzone View -->
                    <template x-if="!imagePreviewUrl">
                        <div class="space-y-4 py-4">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800/60 mx-auto flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Upload or Capture Sales Order Image</h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Supports JPG, PNG, WEBP up to 5MB. Click camera to snap photo live.</p>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                                <button type="button" @click="openCamera()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Open Camera & Snap
                                </button>

                                <button type="button" @click="document.getElementById('mobile_camera_input').click()" class="sm:hidden inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold transition">
                                    📱 Mobile Camera
                                </button>

                                <button type="button" @click="document.getElementById('so_image').click()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Choose File from Disk
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Section 3: Optional Proof & Notes -->
            <div class="space-y-4 pt-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Proof & Notes
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="slip_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Slip / Payment Proof <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <input type="file" id="slip_image" name="slip_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>

                    <div>
                        <label for="remarks" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="2" placeholder="Brief notes..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Description</label>
                    <textarea id="description" name="description" rows="2" placeholder="Detailed breakdown..."
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Submit Button Footer -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <p class="text-[11px] text-slate-400">* Required fields must be completed before submission.</p>
                <div class="flex gap-3">
                    <a href="{{ route('admin.upload-sos.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Sales Order
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Live Camera Webcam Modal -->
    <div x-show="showCameraModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="closeCamera()" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full p-6 shadow-2xl space-y-4 relative">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                    </span>
                    Live Camera Stream
                </h3>
                <button type="button" @click="closeCamera()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">✕</button>
            </div>

            <template x-if="cameraError">
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs">
                    <p x-text="cameraError"></p>
                </div>
            </template>

            <!-- Video Container: Clean Unobstructed Camera Feed -->
            <div class="bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center border border-slate-800 shadow-inner">
                <video x-ref="cameraVideo" autoplay playsinline class="w-full h-full max-h-[60vh] object-contain"></video>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="closeCamera()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </button>
                <button type="button" @click="capturePhoto()" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-lg shadow-rose-600/25 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Take Snapshot
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
