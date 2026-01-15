import './bootstrap';

const state = {
	token: null,
	user: null,
};

const api = async (path, { method = 'GET', body, auth = false } = {}) => {
	const headers = { 'Content-Type': 'application/json' };
	if (auth && state.token) {
		headers.Authorization = `Bearer ${state.token}`;
	}

	const res = await fetch(`/api${path}`, {
		method,
		headers,
		body: body ? JSON.stringify(body) : undefined,
	});

	if (!res.ok) {
		const detail = await res.text();
		throw new Error(detail || `${res.status}`);
	}

	return res.status === 204 ? null : res.json();
};

const setStatus = (el, message, tone = 'neutral') => {
	if (!el) return;
	const toneClass = tone === 'error' ? 'text-red-600' : tone === 'success' ? 'text-emerald-600' : 'text-slate-600';
	el.className = `text-sm mt-2 ${toneClass}`;
	el.textContent = message;
};

const renderToken = () => {
	const tokenEl = document.querySelector('[data-token]');
	if (!tokenEl) return;

	if (state.token) {
		tokenEl.textContent = state.token;
		tokenEl.parentElement.classList.remove('hidden');
	} else {
		tokenEl.textContent = '';
		tokenEl.parentElement.classList.add('hidden');
	}
};

const populatePrefeituras = async () => {
	const select = document.querySelector('[data-prefeitura-select]');
	if (!select) return;
	try {
		const list = await api('/prefeituras');
		select.innerHTML = '';
		if (!list.length) {
			const opt = document.createElement('option');
			opt.value = '';
			opt.textContent = 'Nenhuma prefeitura cadastrada';
			select.appendChild(opt);
			select.disabled = true;
			return;
		}
		list.forEach((p) => {
			const opt = document.createElement('option');
			opt.value = p.id;
			opt.textContent = `${p.nome ?? p.name ?? 'Prefeitura'} (${p.id})`;
			select.appendChild(opt);
		});
		select.value = list[0].id;
		select.disabled = false;
	} catch (err) {
		setStatus(document.querySelector('[data-prefeitura-status]'), `Falha ao carregar prefeituras: ${err.message}`, 'error');
	}
};

const renderProblems = (items, targetSelector) => {
	const container = document.querySelector(targetSelector);
	if (!container) return;
	container.innerHTML = '';
	if (!items || !items.length) {
		container.innerHTML = '<p class="text-sm text-slate-600">Nada encontrado.</p>';
		return;
	}

	items.forEach((item) => {
		const card = document.createElement('div');
		card.className = 'border border-slate-200 rounded-lg p-3 flex flex-col gap-1 bg-white shadow-sm';
		card.innerHTML = `
			<div class="text-xs text-slate-500">#${item.id} · ${item.status}</div>
			<div class="font-semibold text-slate-900">${item.titulo}</div>
			<div class="text-sm text-slate-700">${item.descricao}</div>
			<div class="text-xs text-slate-600">${item.bairro}${item.cidade ? ' · ' + item.cidade : ''}${item.uf ? '/' + item.uf : ''}</div>
		`;
		container.appendChild(card);
	});
};

document.addEventListener('DOMContentLoaded', () => {
	renderToken();
	populatePrefeituras();

	const loginForm = document.querySelector('[data-form-login]');
	const logoutBtn = document.querySelector('[data-btn-logout]');
	const submitForm = document.querySelector('[data-form-problema]');
	const mineBtn = document.querySelector('[data-btn-mine]');
	const adminBtn = document.querySelector('[data-btn-admin-list]');

	loginForm?.addEventListener('submit', async (e) => {
		e.preventDefault();
		const status = document.querySelector('[data-login-status]');
		const email = loginForm.querySelector('input[name=email]')?.value || '';
		const password = loginForm.querySelector('input[name=password]')?.value || '';
		try {
			const data = await api('/auth/login', { method: 'POST', body: { email, password } });
			state.token = data.token;
			state.user = data.user;
			renderToken();
			setStatus(status, `Autenticado como ${data.user.name}`, 'success');
		} catch (err) {
			setStatus(status, `Erro: ${err.message}`, 'error');
		}
	});

	logoutBtn?.addEventListener('click', async () => {
		try {
			await api('/auth/logout', { method: 'POST', auth: true });
		} catch (err) {
			// ignore failures on logout
		}
		state.token = null;
		state.user = null;
		renderToken();
		setStatus(document.querySelector('[data-login-status]'), 'Sessão encerrada.', 'neutral');
	});

	submitForm?.addEventListener('submit', async (e) => {
		e.preventDefault();
		const status = document.querySelector('[data-submit-status]');
		const formData = new FormData(submitForm);
		const body = Object.fromEntries(formData.entries());
		['latitude', 'longitude'].forEach((k) => {
			if (body[k] === '') delete body[k];
			else body[k] = Number(body[k]);
		});
		try {
			const created = await api('/problemas', { method: 'POST', body, auth: !!state.token });
			setStatus(status, `Protocolo #${created.id} criado.`, 'success');
			submitForm.reset();
			await populatePrefeituras();
		} catch (err) {
			setStatus(status, `Erro ao enviar: ${err.message}`, 'error');
		}
	});

	mineBtn?.addEventListener('click', async () => {
		const status = document.querySelector('[data-mine-status]');
		try {
			const items = await api('/problemas/mine', { auth: true });
			renderProblems(items, '[data-mine-list]');
			setStatus(status, `Encontrados ${items.length} registros.`, 'success');
		} catch (err) {
			setStatus(status, `Erro: ${err.message}`, 'error');
		}
	});

	adminBtn?.addEventListener('click', async () => {
		const status = document.querySelector('[data-admin-status]');
		const filterStatus = document.querySelector('[data-admin-filter-status]')?.value;
		const params = filterStatus ? `?status=${filterStatus}` : '';
		try {
			const page = await api(`/admin/problemas${params}`, { auth: true });
			renderProblems(page.data, '[data-admin-list]');
			setStatus(status, `Página ${page.current_page} de ${page.last_page}.`, 'success');
		} catch (err) {
			setStatus(status, `Erro: ${err.message}`, 'error');
		}
	});
});
