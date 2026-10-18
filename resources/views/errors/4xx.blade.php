<x-error-page :code="$exception->getStatusCode()" icon="bi-exclamation-triangle"
    title="Permintaan Tidak Bisa Diproses"
    message="Terjadi masalah saat memproses permintaan Anda." />
