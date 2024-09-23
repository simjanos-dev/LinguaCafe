export default {
    root: {
        class: [
            'relative',

            // Flexbox & Alignment
            'inline-flex',
            'align-bottom',

            // Size
            'w-5 h-5',

            // Misc
            'cursor-pointer',
            'select-none'
        ]
    },
    box: ({ props, context }) => ({
        class: [
            // Flexbox
            'flex justify-center items-center',

            // Size
            'w-5 h-5',

            // Shape
            'border outline-transparent',
            'rounded-full',

            // Transition
            'transition duration-200 ease-in-out',

            // Colors
            {
                'text-surface-700 dark:text-white/80': context.checked,
                'border-surface-300 dark:border-surface-700': !context.checked && !props.invalid,
                'border-primary bg-primary': context.checked && !props.disabled
            },
            // Invalid State
            { 'border-red-500 dark:border-red-400': props.invalid },

            // States
            {
                'peer-hover:border-surface-400 dark:peer-hover:border-surface-400': !props.disabled && !props.invalid && !context.checked,
                'peer-hover:border-primary-emphasis': !props.disabled && !context.checked,
                'peer-hover:[&>*:first-child]:bg-primary-600 dark:peer-hover:[&>*:first-child]:bg-primary-300': !props.disabled && !context.checked,
                'peer-focus-visible:ring-1 peer-focus-visible:ring-primary-500 dark:peer-focus-visible:ring-primary-400': !props.disabled,
                'bg-surface-200 [&>*:first-child]:bg-surface-600 dark:bg-surface-700 dark:[&>*:first-child]:bg-surface-400 border-surface-300 dark:border-surface-700 select-none pointer-events-none cursor-default': props.disabled
            }
        ]
    }),
    input: {
        class: [
            'peer',

            // Size
            'w-full ',
            'h-full',

            // Position
            'absolute',
            'top-0 left-0',
            'z-10',

            // Spacing
            'p-0',
            'm-0',

            // Shape
            'opacity-0',
            'rounded-md',
            'outline-none',
            'border-1 border-surface-200 dark:border-surface-700',

            // Misc
            'appearance-none',
            'cursor-pointer'
        ]
    },
    icon: ({ context }) => ({
        class: [
            'block',

            // Shape
            'rounded-full',

            // Size
            'w-3 h-3',

            // Conditions
            {
                'bg-surface-0 dark:bg-surface-900': context.checked,
                'bg-primary': !context.checked,
                'backface-hidden invisible scale-[0.1]': !context.checked,
                'transform visible translate-z-0 scale-[1,1]': context.checked
            },

            // Transition
            'transition duration-200'
        ]
    })
};
