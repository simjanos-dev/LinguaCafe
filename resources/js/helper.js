export function formatNumber(amount) {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HUF',
        minimumFractionDigits: 0,
    });
      
    return formatter.format(amount).replace('HUF', '');
}