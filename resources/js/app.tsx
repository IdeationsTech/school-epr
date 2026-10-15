import React from 'react';
import {createRoot} from 'react-dom/client';
import {Database, Layers, BookOpen, CheckCircle, GraduationCap} from 'lucide-react';

const modules = [
    ['School & access', 'Schools, campuses, memberships and role permissions'],
    ['Students & families', 'Admissions, enrollment history and guardian links'],
    ['Fees & finance', 'Invoices, payments, allocations, reversals and closing'],
    ['Academics & diary', 'Attendance, subject diary revisions and manual sharing'],
    ['Tests & exams', 'Marks, grading versions and consolidated report snapshots'],
    ['Staff & payroll', 'Salary structures, advances, payroll and payments'],
];
function App() {
    return <div className="min-h-screen md:flex">
        <aside className="bg-[#14263d] text-white p-6 md:w-64 md:min-h-screen">
            <div className="flex items-center gap-3 font-bold text-xl"><GraduationCap/> School ERP</div>
            <p className="text-slate-300 text-sm mt-2">Project foundation</p>
            <div className="mt-10 flex items-center gap-3 rounded-xl bg-teal-800 p-3"><Layers size={18}/> Setup overview</div>
            <p className="mt-8 text-sm text-slate-300">Full module screens and authentication will be implemented in the next phases.</p>
        </aside>
        <main className="flex-1 p-5 md:p-10 max-w-7xl">
            <p className="text-teal-700 font-semibold text-sm">LARAVEL · REACT · MYSQL</p>
            <h1 className="text-3xl font-bold mt-2">Your school platform starts here.</h1>
            <p className="mt-3 text-slate-600 max-w-2xl">This is the project and database foundation, not the finished school software. No real school records are exposed on this page.</p>
            <div className="mt-8 grid gap-4 sm:grid-cols-3">
                {[['83', 'Domain tables', Database], ['46', 'Planned screens', BookOpen], ['MySQL', 'Persistent storage', CheckCircle]].map(([value,label,Icon])=>{
                    const Symbol=Icon as typeof Database;
                    return <div key={String(label)} className="bg-white border border-slate-200 rounded-xl p-5">
                        <Symbol className="text-teal-700 mb-3"/><p className="text-2xl font-bold">{String(value)}</p><p className="text-sm text-slate-500">{String(label)}</p>
                    </div>;
                })}
            </div>
            <h2 className="font-semibold text-xl mt-10 mb-4">Database modules</h2>
            <div className="grid gap-4 lg:grid-cols-2">{modules.map(([title,body])=>
                <section key={title} className="bg-white rounded-xl border border-slate-200 p-5"><h3 className="font-semibold">{title}</h3><p className="text-slate-600 mt-2 text-sm">{body}</p></section>)}
            </div>
            <div className="mt-8 border border-teal-200 bg-teal-50 p-5 rounded-xl text-sm">
                <strong>Next:</strong> complete the Windows setup guide in docs/setup/windows.md, then run the migrations. Future-only front office, complaints and observations have storage definitions only.
            </div>
        </main>
    </div>;
}
const target=document.getElementById('app');
if(target) createRoot(target).render(<App/>);
