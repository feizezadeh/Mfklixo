'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch } from '@/lib/api';
import {
  Cpu,
  Plus,
  RefreshCw,
  Play,
  Square,
  RotateCw,
  Trash2,
  Terminal,
  Package,
  Hammer,
  ExternalLink,
  CheckCircle2,
  AlertCircle,
  X,
} from 'lucide-react';

interface AppItem {
  name: string;
  owner: string;
  runtime: 'nodejs' | 'python' | 'react' | string;
  domain: string;
  port: number;
  app_dir: string;
  start_cmd: string;
  env_vars: string;
  status: 'running' | 'stopped' | 'static' | string;
}

export default function AppsPage() {
  const [apps, setApps] = useState<AppItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<string | null>(null);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);
  const [logsOutput, setLogsOutput] = useState<{ name: string; content: string } | null>(null);

  const [showModal, setShowModal] = useState(false);
  const [name, setName] = useState('');
  const [runtime, setRuntime] = useState<'nodejs' | 'python' | 'react'>('nodejs');
  const [domain, setDomain] = useState('');
  const [port, setPort] = useState('');
  const [startCmd, setStartCmd] = useState('');
  const [envVars, setEnvVars] = useState('');

  const fetchApps = async () => {
    setLoading(true);
    try {
      const data = await apiFetch<{ apps: AppItem[] }>('/apps');
      setApps(data.apps || []);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to load applications', success: false });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchApps();
  }, []);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !domain.trim()) return;
    setActionLoading('create');
    setMessage(null);
    try {
      const res = await apiFetch<{ message: string }>('/apps', {
        method: 'POST',
        body: JSON.stringify({
          name: name.trim(),
          runtime,
          domain: domain.trim(),
          port: port ? Number(port) : undefined,
          start_cmd: startCmd.trim() || undefined,
          env_vars: envVars || undefined,
        }),
      });
      setMessage({ text: res.message, success: true });
      setShowModal(false);
      setName('');
      setDomain('');
      setPort('');
      setStartCmd('');
      setEnvVars('');
      await fetchApps();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to deploy application', success: false });
    } finally {
      setActionLoading(null);
    }
  };

  const handleAction = async (appName: string, action: string) => {
    setActionLoading(`${appName}:${action}`);
    setMessage(null);
    try {
      const res = await apiFetch<{ message: string; output?: string }>('/apps/action', {
        method: 'POST',
        body: JSON.stringify({ name: appName, action }),
      });
      if (action === 'logs' || action === 'install' || action === 'build') {
        setLogsOutput({ name: `${appName} (${action})`, content: res.output || 'No output.' });
      } else {
        setMessage({ text: res.message, success: true });
      }
      await fetchApps();
    } catch (err: any) {
      setMessage({ text: err.message || `Action ${action} failed`, success: false });
    } finally {
      setActionLoading(null);
    }
  };

  const handleDelete = async (appName: string) => {
    if (!confirm(`Remove application "${appName}" and its Nginx/systemd configuration?`)) return;
    setActionLoading(`${appName}:delete`);
    setMessage(null);
    try {
      const res = await apiFetch<{ message: string }>(`/apps?name=${encodeURIComponent(appName)}`, {
        method: 'DELETE',
      });
      setMessage({ text: res.message, success: true });
      await fetchApps();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete application', success: false });
    } finally {
      setActionLoading(null);
    }
  };

  const runtimeBadge = (rt: string) => {
    switch (rt) {
      case 'nodejs':
        return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
      case 'python':
        return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
      case 'react':
        return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
      default:
        return 'bg-slate-500/10 text-slate-300 border-slate-500/20';
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Cpu className="h-6 w-6 text-sky-400" />
              App Manager (Node.js, Python & React)
            </h1>
            <p className="text-sm text-slate-400">
              Deploy and manage isolated Node.js, Python, and React SPA applications with automatic Nginx reverse proxy
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={fetchApps}
              disabled={loading}
              className="flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-colors"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowModal(true)}
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Create Application
            </button>
          </div>
        </div>

        {message && (
          <div
            className={`flex items-center justify-between rounded-2xl border p-4 ${
              message.success
                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                : 'border-rose-500/20 bg-rose-500/10 text-rose-400'
            }`}
          >
            <div className="flex items-center gap-3">
              {message.success ? <CheckCircle2 className="h-5 w-5" /> : <AlertCircle className="h-5 w-5" />}
              <span className="text-sm font-medium">{message.text}</span>
            </div>
            <button onClick={() => setMessage(null)} className="text-slate-400 hover:text-white">
              <X className="h-4 w-4" />
            </button>
          </div>
        )}

        {loading ? (
          <div className="flex h-64 items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50">
            <RefreshCw className="h-8 w-8 animate-spin text-sky-400" />
          </div>
        ) : apps.length === 0 ? (
          <div className="flex flex-col items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50 p-12 text-center">
            <Cpu className="h-12 w-12 text-slate-600 mb-3" />
            <h3 className="text-lg font-semibold text-white">No Applications Deployed</h3>
            <p className="text-sm text-slate-400 mt-1 max-w-md">
              Launch a Node.js API, Python (Flask/FastAPI/Django) service, or React SPA with one click.
            </p>
          </div>
        ) : (
          <div className="overflow-hidden rounded-3xl border border-white/5 bg-slate-900/50 shadow-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-400">
                <thead className="bg-white/[0.02] text-xs uppercase text-slate-300 border-b border-white/5">
                  <tr>
                    <th className="px-6 py-4">App Name</th>
                    <th className="px-6 py-4">Runtime</th>
                    <th className="px-6 py-4">Domain & Port</th>
                    <th className="px-6 py-4">Directory</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {apps.map((app) => (
                    <tr key={app.name} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4">
                        <div className="font-semibold text-white">{app.name}</div>
                        <div className="text-xs text-slate-500">Owner: {app.owner}</div>
                      </td>
                      <td className="px-6 py-4">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase border ${runtimeBadge(app.runtime)}`}>
                          {app.runtime}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <a
                          href={`http://${app.domain}`}
                          target="_blank"
                          rel="noreferrer"
                          className="inline-flex items-center gap-1 text-sky-400 hover:underline font-medium"
                        >
                          {app.domain}
                          <ExternalLink className="h-3 w-3" />
                        </a>
                        <div className="text-xs text-slate-500">
                          {app.runtime === 'react' ? 'Nginx SPA Static' : `127.0.0.1:${app.port}`}
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <code className="text-xs text-slate-300 bg-black/30 px-2 py-1 rounded">{app.app_dir}</code>
                      </td>
                      <td className="px-6 py-4">
                        {app.status === 'running' || app.status === 'static' ? (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                            {app.status === 'static' ? 'Active (SPA)' : 'Running'}
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span className="h-1.5 w-1.5 rounded-full bg-rose-400" />
                            Stopped
                          </span>
                        )}
                      </td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-1.5">
                          {app.runtime !== 'react' && (
                            <>
                              <button
                                onClick={() => handleAction(app.name, 'restart')}
                                disabled={actionLoading !== null}
                                title="Start / Restart"
                                className="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-1.5 text-emerald-400 hover:bg-emerald-500/20"
                              >
                                <RotateCw className="h-4 w-4" />
                              </button>
                              <button
                                onClick={() => handleAction(app.name, 'stop')}
                                disabled={actionLoading !== null}
                                title="Stop Service"
                                className="rounded-lg border border-amber-500/20 bg-amber-500/10 p-1.5 text-amber-400 hover:bg-amber-500/20"
                              >
                                <Square className="h-4 w-4" />
                              </button>
                            </>
                          )}
                          <button
                            onClick={() => handleAction(app.name, 'install')}
                            disabled={actionLoading !== null}
                            title={app.runtime === 'python' ? 'Install requirements.txt (venv)' : 'Run npm install'}
                            className="rounded-lg border border-sky-500/20 bg-sky-500/10 px-2 py-1.5 text-xs font-medium text-sky-400 hover:bg-sky-500/20 flex items-center gap-1"
                          >
                            <Package className="h-3.5 w-3.5" />
                            {app.runtime === 'python' ? 'pip' : 'npm i'}
                          </button>
                          {app.runtime !== 'python' && (
                            <button
                              onClick={() => handleAction(app.name, 'build')}
                              disabled={actionLoading !== null}
                              title="Run npm run build"
                              className="rounded-lg border border-indigo-500/20 bg-indigo-500/10 px-2 py-1.5 text-xs font-medium text-indigo-400 hover:bg-indigo-500/20 flex items-center gap-1"
                            >
                              <Hammer className="h-3.5 w-3.5" />
                              Build
                            </button>
                          )}
                          {app.runtime !== 'react' && (
                            <button
                              onClick={() => handleAction(app.name, 'logs')}
                              disabled={actionLoading !== null}
                              title="View Logs"
                              className="rounded-lg border border-white/10 bg-white/5 p-1.5 text-slate-300 hover:bg-white/10"
                            >
                              <Terminal className="h-4 w-4" />
                            </button>
                          )}
                          <button
                            onClick={() => handleDelete(app.name)}
                            disabled={actionLoading !== null}
                            title="Delete App"
                            className="rounded-lg p-1.5 text-slate-500 hover:bg-rose-500/10 hover:text-rose-400"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>

      {/* Create App Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Cpu className="h-5 w-5 text-sky-400" />
                Deploy New Application
              </h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="mt-4 space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase text-slate-400 mb-1">App Name</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. my-api"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium uppercase text-slate-400 mb-1">Runtime Stack</label>
                  <select
                    value={runtime}
                    onChange={(e) => setRuntime(e.target.value as 'nodejs' | 'python' | 'react')}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none"
                  >
                    <option value="nodejs">Node.js (Express / Next.js / Nest)</option>
                    <option value="python">Python (FastAPI / Django / Flask)</option>
                    <option value="react">React / Vite / Vue (Static SPA)</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div className="col-span-2">
                  <label className="block text-xs font-medium uppercase text-slate-400 mb-1">Domain / Subdomain</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. app.example.com"
                    value={domain}
                    onChange={(e) => setDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium uppercase text-slate-400 mb-1">Internal Port</label>
                  <input
                    type="number"
                    placeholder="Auto (4001+)"
                    disabled={runtime === 'react'}
                    value={port}
                    onChange={(e) => setPort(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none disabled:opacity-50"
                  />
                </div>
              </div>

              {runtime !== 'react' && (
                <div>
                  <label className="block text-xs font-medium uppercase text-slate-400 mb-1">
                    Start Command (Optional)
                  </label>
                  <input
                    type="text"
                    placeholder={runtime === 'nodejs' ? '/usr/bin/node server.js' : '/usr/bin/python3 app.py'}
                    value={startCmd}
                    onChange={(e) => setStartCmd(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white font-mono focus:border-sky-500 focus:outline-none"
                  />
                </div>
              )}

              <div>
                <label className="block text-xs font-medium uppercase text-slate-400 mb-1">
                  Environment Variables (.env)
                </label>
                <textarea
                  rows={3}
                  placeholder="API_KEY=secret&#10;DB_HOST=127.0.0.1"
                  value={envVars}
                  onChange={(e) => setEnvVars(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-xs text-white font-mono focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading === 'create'}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg"
                >
                  {actionLoading === 'create' ? 'Deploying...' : 'Deploy Application'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Logs / Output Modal */}
      {logsOutput && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-2xl rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-3 border-b border-white/5">
              <h3 className="text-sm font-bold text-white flex items-center gap-2">
                <Terminal className="h-4 w-4 text-sky-400" />
                Output: {logsOutput.name}
              </h3>
              <button onClick={() => setLogsOutput(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>
            <pre className="mt-4 max-h-96 overflow-auto rounded-xl bg-black/80 p-4 text-xs font-mono text-slate-300 whitespace-pre-wrap">
              {logsOutput.content}
            </pre>
          </div>
        </div>
      )}
    </Shell>
  );
}
