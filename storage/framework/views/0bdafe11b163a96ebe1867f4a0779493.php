<?php $__env->startSection('title', 'Kontak'); ?>
<?php $__env->startSection('content'); ?>
<section class="simple-page">
    <span class="eyebrow" data-reveal><span class="eyebrow-index">01</span>Contact</span>
    <h1 data-reveal>Terhubung dengan <em>pengelola sekolah.</em></h1>
    <p data-reveal>Untuk bantuan akun atau pertanyaan mengenai fasilitas, hubungi admin sekolah melalui kanal komunikasi resmi yang ditetapkan sekolah.</p>

    <div class="manifesto" data-reveal-group>
        <div data-reveal>
            <b>01 / AKUN</b>
            <h2>Akses dan pendaftaran</h2>
            <p>Akun pelapor dapat dibuat melalui halaman daftar. Akun petugas dan admin dikelola oleh administrator sekolah.</p>
        </div>
        <div data-reveal>
            <b>02 / FASILITAS</b>
            <h2>Pertanyaan teknis</h2>
            <p>Sampaikan detail kerusakan melalui laporan agar tercatat lengkap, termasuk lokasi dan dokumentasi pendukung.</p>
        </div>
        <div data-reveal>
            <b>03 / TINDAK LANJUT</b>
            <h2>Status laporan</h2>
            <p>Pantau perkembangan laporan dari dashboard akun. Catatan penanganan akan tampil pada setiap perubahan status.</p>
        </div>
    </div>

    <blockquote class="page-quote" data-reveal>“Kanal yang jelas membuat setiap laporan sampai ke orang yang tepat.”</blockquote>

    <div class="access-panel" data-reveal>
        <div>
            <h2>Mulai dari akunmu.</h2>
            <p>Masuk untuk memantau laporan, atau buat akun baru jika belum memiliki akses.</p>
        </div>
        <div class="access-panel-actions">
            <?php if(auth()->guard()->check()): ?>
                <a class="button button-primary" href="<?php echo e(route('reporter.dashboard')); ?>">Buka dashboard <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></a>
            <?php else: ?>
                <a class="button button-primary" href="<?php echo e(route('login')); ?>">Masuk <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></a>
                <a class="button button-ghost" href="<?php echo e(route('register')); ?>">Daftar akun</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\manusacare\resources\views/public/contact.blade.php ENDPATH**/ ?>