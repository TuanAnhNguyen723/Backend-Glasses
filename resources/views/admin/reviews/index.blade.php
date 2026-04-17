@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Quản Lý Bình Luận')

@section('header')
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Quản Lý Bình Luận</h2>
            <p class="text-[#4c739a] text-sm font-medium">Duyệt và phản hồi bình luận khách hàng ngay tại trang quản trị.</p>
        </div>
    </header>
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <input id="search" type="text" placeholder="Tìm theo sản phẩm / khách / nội dung..."
                class="md:col-span-2 w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
            <select id="rating-filter" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3">
                <option value="">Tất cả số sao</option>
                <option value="5">5 sao</option>
                <option value="4">4 sao</option>
                <option value="3">3 sao</option>
                <option value="2">2 sao</option>
                <option value="1">1 sao</option>
            </select>
            <select id="reply-filter" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3">
                <option value="">Tất cả phản hồi</option>
                <option value="replied">Đã phản hồi</option>
                <option value="unreplied">Chưa phản hồi</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-[#e7edf3] dark:border-slate-700">
                        <th class="py-3 px-3">Sản phẩm</th>
                        <th class="py-3 px-3">Khách hàng</th>
                        <th class="py-3 px-3">Đánh giá</th>
                        <th class="py-3 px-3">Bình luận</th>
                        <th class="py-3 px-3">Phản hồi shop</th>
                        <th class="py-3 px-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="reviews-tbody"></tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <p id="pagination-text" class="text-xs text-[#4c739a]"></p>
            <div class="flex gap-2">
                <button id="prev-btn" class="px-3 py-2 text-xs font-bold rounded-lg border border-[#cfdbe7] dark:border-slate-700">Trước</button>
                <button id="next-btn" class="px-3 py-2 text-xs font-bold rounded-lg border border-[#cfdbe7] dark:border-slate-700">Sau</button>
            </div>
        </div>
    </div>

    <div id="reply-modal" class="hidden fixed inset-0 z-[120]">
        <div id="reply-modal-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="relative h-full w-full flex items-center justify-center p-4">
            <div class="w-full max-w-xl rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl">
                <div class="px-5 py-4 border-b border-[#e7edf3] dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-base font-bold">Phản Hồi Khách Hàng</h3>
                    <button id="reply-close-btn" class="h-8 w-8 rounded-lg hover:bg-[#f1f5f9] dark:hover:bg-slate-800">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <p class="text-xs text-[#4c739a]">Nội dung phản hồi sẽ hiển thị công khai dưới bình luận của khách hàng.</p>
                    <textarea id="reply-message" rows="5" maxlength="2000"
                        class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3"
                        placeholder="Nhập nội dung phản hồi..."></textarea>
                    <p id="reply-error" class="hidden text-xs text-red-500 font-semibold"></p>
                </div>
                <div class="px-5 py-4 border-t border-[#e7edf3] dark:border-slate-700 flex items-center justify-end gap-2">
                    <button id="reply-cancel-btn" class="px-3 py-2 text-xs font-bold rounded-lg border border-[#cfdbe7] dark:border-slate-700">Hủy</button>
                    <button id="reply-submit-btn" class="px-3 py-2 text-xs font-bold rounded-lg bg-primary text-white">Gửi phản hồi</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const state = {
            page: 1,
            lastPage: 1,
            replyingReviewId: null,
            editingReplyId: null,
            replying: false,
        };

        function esc(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        async function loadReviews() {
            const search = document.getElementById('search').value.trim();
            const rating = document.getElementById('rating-filter').value;
            const replyStatus = document.getElementById('reply-filter').value;

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: '10',
            });
            if (search) params.set('search', search);
            if (rating) params.set('rating', rating);
            if (replyStatus) params.set('reply_status', replyStatus);

            const response = await fetch(`{{ route('admin.api.reviews') }}?${params.toString()}`);
            const data = await response.json();

            const tbody = document.getElementById('reviews-tbody');
            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-[#4c739a]">Chưa có bình luận nào.</td></tr>';
            } else {
                tbody.innerHTML = data.data.map((item) => `
                    <tr class="border-b border-[#f1f5f9] dark:border-slate-800 align-top">
                        <td class="py-3 px-3 font-semibold">${esc(item.product?.name || 'N/A')}</td>
                        <td class="py-3 px-3">${esc(item.user?.name || 'N/A')}</td>
                        <td class="py-3 px-3">${'★'.repeat(item.rating)}${'☆'.repeat(5 - item.rating)}</td>
                        <td class="py-3 px-3 max-w-xs">${esc(item.comment || '')}</td>
                        <td class="py-3 px-3 max-w-xs">${esc(item.latest_reply?.message || 'Chưa phản hồi')}</td>
                        <td class="py-3 px-3">
                            <div class="flex justify-end gap-2">
                                ${item.latest_reply?.id ? `
                                    <button onclick="editReply(${item.latest_reply.id}, ${JSON.stringify(item.latest_reply.message || '').replace(/"/g, '&quot;')})" class="px-2 py-1 text-xs rounded-lg border border-[#cfdbe7] dark:border-slate-700">
                                        Sửa phản hồi
                                    </button>
                                ` : `
                                    <button onclick="replyReview(${item.id})" class="px-2 py-1 text-xs rounded-lg bg-primary text-white">
                                        Phản hồi
                                    </button>
                                `}
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            state.lastPage = data.last_page || 1;
            document.getElementById('pagination-text').textContent = `Trang ${data.current_page || 1}/${state.lastPage} - Tổng ${data.total || 0} bình luận`;
            document.getElementById('prev-btn').disabled = (data.current_page || 1) <= 1;
            document.getElementById('next-btn').disabled = (data.current_page || 1) >= state.lastPage;
        }

        function openReplyModal(reviewId, editingReplyId = null, initialMessage = '') {
            state.replyingReviewId = reviewId;
            state.editingReplyId = editingReplyId;
            const modal = document.getElementById('reply-modal');
            const message = document.getElementById('reply-message');
            const error = document.getElementById('reply-error');
            const submitBtn = document.getElementById('reply-submit-btn');
            error.classList.add('hidden');
            error.textContent = '';
            message.value = initialMessage || '';
            submitBtn.textContent = editingReplyId ? 'Lưu chỉnh sửa' : 'Gửi phản hồi';
            modal.classList.remove('hidden');
            setTimeout(() => message.focus(), 0);
        }

        function closeReplyModal() {
            if (state.replying) return;
            state.replyingReviewId = null;
            state.editingReplyId = null;
            const modal = document.getElementById('reply-modal');
            const message = document.getElementById('reply-message');
            const error = document.getElementById('reply-error');
            const submitBtn = document.getElementById('reply-submit-btn');
            error.classList.add('hidden');
            error.textContent = '';
            message.value = '';
            submitBtn.textContent = 'Gửi phản hồi';
            modal.classList.add('hidden');
        }

        function setReplySubmitting(isSubmitting) {
            state.replying = isSubmitting;
            const submitBtn = document.getElementById('reply-submit-btn');
            const cancelBtn = document.getElementById('reply-cancel-btn');
            const closeBtn = document.getElementById('reply-close-btn');
            submitBtn.disabled = isSubmitting;
            cancelBtn.disabled = isSubmitting;
            closeBtn.disabled = isSubmitting;
            submitBtn.textContent = isSubmitting ? 'Đang gửi...' : 'Gửi phản hồi';
        }

        async function submitReply() {
            const reviewId = state.replyingReviewId;
            const editingReplyId = state.editingReplyId;
            if (!reviewId && !editingReplyId) return;
            const messageEl = document.getElementById('reply-message');
            const errorEl = document.getElementById('reply-error');
            const message = messageEl.value.trim();
            const isEditing = !!editingReplyId;
            if (!isEditing && !message) {
                errorEl.textContent = 'Vui lòng nhập nội dung phản hồi.';
                errorEl.classList.remove('hidden');
                messageEl.focus();
                return;
            }
            if (message.length > 2000) {
                errorEl.textContent = 'Nội dung phản hồi tối đa 2000 ký tự.';
                errorEl.classList.remove('hidden');
                return;
            }
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
            setReplySubmitting(true);

            try {
                const response = await fetch(
                    isEditing
                        ? `{{ url('/admin/api/replies') }}/${editingReplyId}`
                        : `{{ url('/admin/api/reviews') }}/${reviewId}/reply`,
                    {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: message.trim() }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Không thể lưu phản hồi');
                notificationManager.success(data.message || (isEditing ? 'Đã cập nhật phản hồi' : 'Đã phản hồi bình luận'));
                setReplySubmitting(false);
                closeReplyModal();
                await loadReviews();
            } catch (error) {
                notificationManager.error(error.message, 'Lỗi');
            } finally {
                setReplySubmitting(false);
            }
        }

        document.getElementById('search').addEventListener('input', () => {
            state.page = 1;
            loadReviews();
        });
        document.getElementById('rating-filter').addEventListener('change', () => {
            state.page = 1;
            loadReviews();
        });
        document.getElementById('reply-filter').addEventListener('change', () => {
            state.page = 1;
            loadReviews();
        });
        document.getElementById('prev-btn').addEventListener('click', () => {
            if (state.page > 1) {
                state.page--;
                loadReviews();
            }
        });
        document.getElementById('next-btn').addEventListener('click', () => {
            if (state.page < state.lastPage) {
                state.page++;
                loadReviews();
            }
        });
        document.getElementById('reply-submit-btn').addEventListener('click', submitReply);
        document.getElementById('reply-cancel-btn').addEventListener('click', closeReplyModal);
        document.getElementById('reply-close-btn').addEventListener('click', closeReplyModal);
        document.getElementById('reply-modal-overlay').addEventListener('click', closeReplyModal);
        document.getElementById('reply-message').addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                submitReply();
            }
            if (e.key === 'Escape') {
                closeReplyModal();
            }
        });

        function replyReview(id) {
            openReplyModal(id, null, '');
        }

        function editReply(replyId, currentMessage) {
            openReplyModal(0, replyId, currentMessage || '');
        }

        document.addEventListener('DOMContentLoaded', loadReviews);
    </script>
@endpush
