@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
	'class' => 'block w-full px-3 py-2 rounded-md shadow-sm border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:!border-gray-700 dark:!bg-gray-900 dark:!text-gray-100 dark:placeholder-gray-400'
]) }}>
