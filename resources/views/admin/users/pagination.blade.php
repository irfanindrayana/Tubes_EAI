<div class="d-flex justify-content-between align-items-center">
    <div>
        Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
    </div>
    {{ $users->links() }}
</div>
