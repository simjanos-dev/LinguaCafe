export default {
    root: 'has-[[data-pc-name=stepitem]]:flex has-[[data-pc-name=stepitem]]:flex-col',
    separator: 'flex-1 w-full h-[2px] bg-surface-200 dark:bg-surface-700 transition-shadow duration-200',
    transition: {
        class: ['flex flex-1', 'bg-surface-0 dark:bg-surface-900', 'text-surface-900 dark:text-surface-0'],
        enterFromClass: 'max-h-0',
        enterActiveClass: 'overflow-hidden transition-[max-height] duration-1000 ease-[cubic-bezier(0.42,0,0.58,1)]',
        enterToClass: 'max-h-[1000px]',
        leaveFromClass: 'max-h-[1000px]',
        leaveActiveClass: 'overflow-hidden transition-[max-height] duration-[450ms] ease-[cubic-bezier(0,1,0,1)]',
        leaveToClass: 'max-h-0'
    }
};
