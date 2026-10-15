let csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
export class ApiError extends Error {
    constructor(message: string, public status: number) { super(message); }
}
export async function api<T>(path: string, method = 'GET', body?: unknown): Promise<T> {
    const response = await fetch(`/app-api${path}`, {
        method, credentials: 'same-origin',
        headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        ...(body === undefined ? {} : {body:JSON.stringify(body)}),
    });
    const data = await response.json().catch(() => ({}));
    if(data.csrf_token) csrf=data.csrf_token;
    if(!response.ok) {
        const errors=data.errors ? Object.values(data.errors).flat().join(' ') : null;
        throw new ApiError(response.status===419 ? 'Session expired. Refresh this page and try again.' : String(errors ?? data.message ?? 'Request failed.'), response.status);
    }
    return data as T;
}
