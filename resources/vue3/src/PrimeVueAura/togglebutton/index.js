export default {
    root: ({ props, context }) => ({
        class: [
            'relative',

            // Alignment
            'flex items-center justify-center',
            'py-2 px-4',
            'rounded-md border',

            // Color
            'bg-surface-100 dark:bg-surface-950',
            {
                'text-surface-600 dark:text-white/60 before:bg-transparent': !context.active,
                'text-surface-800 dark:text-white/80 before:bg-surface-0 dark:before:bg-surface-800': context.active
            },

            // States
            {
                'hover:text-surface-800 dark:hover:text-white/80': !props.disabled && !props.modelValue,
                'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary-500 dark:focus-visible:ring-primary-400': !props.disabled
            },

            // Invalid State
            {
                'border-red-500 dark:border-red-400': props.invalid,
                'border-surface-100 dark:border-surface-950': !props.invalid
            },

            // Before
            'before:absolute before:left-1 before:top-1 before:w-[calc(100%-0.5rem)] before:h-[calc(100%-0.5rem)] before:rounded-[4px] before:z-0',

            // Transitions
            'transition-all duration-200',

            // Misc
            { 'cursor-pointer': !props.disabled, 'opacity-60 select-none pointer-events-none cursor-default': props.disabled }
        ]
    }),
    content: 'relative items-center inline-flex justify-center gap-2',
    label: 'font-medium leading-[normal] text-center w-full z-10 relative',
    icon: 'relative z-10 mr-2'
};
