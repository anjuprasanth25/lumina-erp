import React from "react";
import { useForm } from "@inertiajs/react";

export default function SetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token || "",
        email: email || "",
        password: "",
        password_confirmation: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("password.update"));
    };

    return (
        /* Matches your main layout: bg-lumina-darkBg (#0b0f19) */
        <div className="min-h-screen flex items-center justify-center bg-[#0b0f19] px-4 text-white">
            {/* Card Container (#131b2e) */}
            <div className="max-w-md w-full bg-[#131b2e] p-8 rounded-xl border border-[#23314d] shadow-2xl">
                <h2 className="text-2xl font-bold text-white mb-2">
                    Set Account Password
                </h2>
                <p className="text-slate-400 text-sm mb-6">
                    Set up a new secure password for{" "}
                    <span className="text-blue-400 font-medium">{email}</span>
                </p>

                {Object.keys(errors).length > 0 && (
                    <div className="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg mb-4 text-sm">
                        <ul className="list-disc list-inside">
                            {Object.entries(errors).map(([field, message]) => (
                                <li key={field}>
                                    <strong>{field}:</strong> {message}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Hidden Inputs */}
                    <input type="hidden" name="token" value={data.token} />
                    <input type="hidden" name="email" value={data.email} />

                    {/* Password Field */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-1">
                            New Password
                        </label>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            className="w-full px-4 py-2.5 rounded-lg bg-[#1c273e] border border-[#2f3e5e] text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                            required
                        />
                        {errors.password && (
                            <p className="mt-1 text-xs text-rose-400">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    {/* Confirm Password Field */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-1">
                            Confirm Password
                        </label>
                        <input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData("password_confirmation", e.target.value)
                            }
                            className="w-full px-4 py-2.5 rounded-lg bg-[#1c273e] border border-[#2f3e5e] text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                            required
                        />
                        {errors.password_confirmation && (
                            <p className="mt-1 text-xs text-rose-400">
                                {errors.password_confirmation}
                            </p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className={`w-full mt-2 py-3 px-4 rounded-lg font-semibold text-sm text-white transition-all shadow-lg ${
                            processing
                                ? "bg-blue-400 cursor-not-allowed opacity-75"
                                : "bg-blue-600 hover:bg-blue-500 active:scale-[0.98]"
                        }`}
                    >
                        {processing
                            ? "Updating Password..."
                            : "Save Password & Login"}
                    </button>
                </form>
            </div>
        </div>
    );
}
