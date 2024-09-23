export default {
    button: ({ props }) => ({
        root: {
            class: [
                // Flex & Alignment
                'flex items-center justify-center',

                // Positioning
                {
                    '!sticky flex ml-auto': props.target === 'parent',
                    '!fixed': props.target === 'window'
                },
                'bottom-[20px] right-[20px]',
                'h-10 w-10 rounded-full shadow-md',
                'text-white dark:text-surface-900 bg-surface-600 dark:bg-surface-700',
                'hover:bg-surface-600 dark:hover:bg-surface-300'
            ]
        }
    }),
    transition: {
        enterFromClass: 'opacity-0',
        enterActiveClass: 'transition-opacity duration-150',
        leaveActiveClass: 'transition-opacity duration-150',
        leaveToClass: 'opacity-0'
    }
};
