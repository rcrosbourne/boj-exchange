import {Head, Link} from '@inertiajs/react';
import {PageProps} from '@/types';

export default function Welcome({auth, laravelVersion, phpVersion}: PageProps<{
    laravelVersion: string,
    phpVersion: string
}>) {
    const stats = [
        {name: 'USD', value: '$156.21', change: '+$0.75', changeType: 'positive'},
        {name: 'GBP', value: '$203.85', change: '+$4.02', changeType: 'negative'},
        {name: 'CAD', value: '$110.32', change: '-1.39%', changeType: 'positive'},
        {name: 'EUR', value: '$163.21', change: '+10.18%', changeType: 'negative'},
    ]

    function classNames(...classes: string[]) {
        return classes.filter(Boolean).join(' ')
    }

    return (
        <>
            <Head title="Welcome"/>
            <div
                className="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
                <div className="sm:fixed sm:top-0 sm:right-0 p-6 text-right">
                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                            className="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                            >
                                Log in
                            </Link>

                            <Link
                                href={route('register')}
                                className="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                            >
                                Register
                            </Link>
                        </>
                    )}
                </div>
                <div className="flex flex-col text-slate-100">
                    <dl className="mx-auto grid grid-cols-1 gap-px bg-gray-900/5 sm:grid-cols-2 lg:grid-cols-4">
                        {stats.map((stat) => (
                            <div
                                key={stat.name}
                                className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8"
                            >
                                <dt className="text-sm font-medium leading-6 text-gray-500">{stat.name}</dt>
                                <dd
                                    className={classNames(
                                        stat.changeType === 'negative' ? 'text-rose-600' : 'text-gray-700',
                                        'text-xs font-medium'
                                    )}
                                >
                                    {stat.change}
                                </dd>
                                <dd className="w-full flex-none text-3xl font-medium leading-10 tracking-tight text-gray-900">
                                    {stat.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                    <h2 className="">BOJ Exchange Rate</h2>

                    <div>
                        <p>1 United States Dollar equals</p>
                        <p>154.70 Jamaican Dollar</p>
                        <p>31 Jul, 11:19am UTC</p>
                        <form>
                            <div>
                                <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                                    Price
                                </label>
                                <div className="relative mt-2 rounded-md shadow-sm sm:max-w-xs">
                                    <div
                                        className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span className="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input
                                        type="text"
                                        name="price"
                                        id="price"
                                        className="block w-full rounded-md border-0 py-1.5 pl-7 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="0.00"
                                    />
                                    <div className="absolute inset-y-0 right-0 flex items-center">
                                        <label htmlFor="currency" className="sr-only">
                                            Currency
                                        </label>
                                        <select
                                            id="currency"
                                            name="currency"
                                            className="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                        >
                                            <option>USD</option>
                                            <option>CAD</option>
                                            <option>EUR</option>
                                            <option>JMD</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                                    Price
                                </label>
                                <div className="relative mt-2 rounded-md shadow-sm">
                                    <div
                                        className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span className="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input
                                        type="text"
                                        name="price"
                                        id="price"
                                        className="block w-full rounded-md border-0 py-1.5 pl-7 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="0.00"
                                    />
                                    <div className="absolute inset-y-0 right-0 flex items-center">
                                        <label htmlFor="currency" className="sr-only">
                                            Currency
                                        </label>
                                        <select
                                            id="currency"
                                            name="currency"
                                            className="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                        >
                                            <option>USD</option>
                                            <option>CAD</option>
                                            <option>EUR</option>
                                            <option>JMD</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button
                                    type="button"
                                    className="rounded-md bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-white/20"
                                >
                                    Button text
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
