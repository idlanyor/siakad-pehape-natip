{{-- Skeleton loader untuk tabel AdminLTE. Tampilkan saat data loading, sembunyikan via JS setelah ready. --}}
<div class="table-skeleton" id="{{ $id ?? 'table-skeleton' }}">
  <table class="table table-bordered">
    <thead>
      <tr>
        @for($i = 0; $i < ($cols ?? 5); $i++)
          <th><div class="skeleton-line" style="width:{{ rand(60,100) }}%;height:14px;background:#e0e0e0;border-radius:4px;"></div></th>
        @endfor
      </tr>
    </thead>
    <tbody>
      @for($r = 0; $r < ($rows ?? 5); $r++)
        <tr>
          @for($c = 0; $c < ($cols ?? 5); $c++)
            <td><div class="skeleton-line" style="width:{{ rand(50,95) }}%;height:12px;background:#f0f0f0;border-radius:3px;"></div></td>
          @endfor
        </tr>
      @endfor
    </tbody>
  </table>
</div>

<style>
  .skeleton-line {
    animation: skeleton-pulse 1.5s ease-in-out infinite;
  }
  @keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  .table-skeleton { margin-bottom: 1rem; }
</style>
