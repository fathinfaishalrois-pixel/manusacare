<?php $__env->startSection('title', 'Tentang'); ?>
<?php $__env->startSection('content'); ?>
<section class="simple-page">
    <span class="eyebrow" data-reveal><span class="eyebrow-index">01</span>About MANUSACARE</span>
    <h1 data-reveal>Building a Better School <em>Together.</em></h1>
    <p data-reveal>MANUSACARE adalah platform pelaporan dan pengelolaan kerusakan fasilitas sekolah yang membantu siswa, guru, tenaga kependidikan, petugas, dan admin bekerja dalam satu alur yang terstruktur.</p>

    <div class="manifesto" data-reveal-group>
        <div data-reveal>
            <b>01 / TERARAH</b>
            <h2>Satu saluran resmi</h2>
            <p>Laporan fasilitas tidak lagi tersebar di percakapan sehari-hari. Semua masuk ke satu tempat yang dapat ditelusuri.</p>
        </div>
        <div data-reveal>
            <b>02 / TERBUKA</b>
            <h2>Progres yang terlihat</h2>
            <p>Pelapor dapat melihat status, catatan penanganan, dan dokumentasi sebelum maupun sesudah perbaikan.</p>
        </div>
        <div data-reveal>
            <b>03 / BERKELANJUTAN</b>
            <h2>Dasar pengambilan keputusan</h2>
            <p>Riwayat kerusakan membantu sekolah memprioritaskan perawatan pada area yang paling sering bermasalah.</p>
        </div>
    </div>

    <blockquote class="page-quote" data-reveal>“Fasilitas yang terawat bukan sekadar bangunan. Ia adalah bagian dari cara sekolah menghargai proses belajar.”</blockquote>

    <div class="access-panel" data-reveal>
        <div>
            <h2>Ikut menjaga fasilitas sekolah.</h2>
            <p>Masuk ke akunmu untuk mengirim laporan baru atau memantau laporan yang sudah ada.</p>
        </div>
        <div class="access-panel-actions">
            <?php if(auth()->guard()->check()): ?>
                <a class="button button-primary" href="<?php echo e(route('reports.create')); ?>">Buat laporan <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-up-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up-right']); ?>
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
                <a class="button button-ghost" href="<?php echo e(route('reporter.dashboard')); ?>">Dashboard</a>
            <?php else: ?>
                <a class="button button-primary" href="<?php echo e(route('register')); ?>">Daftar akun <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-up-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up-right']); ?>
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
                <a class="button button-ghost" href="<?php echo e(route('login')); ?>">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\manusacare\resources\views/public/about.blade.php ENDPATH**/ ?>