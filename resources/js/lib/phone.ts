/**
 * Formats a US phone number as 999-999-9999. A leading country code 1 is dropped.
 * Anything that isn't a 10-digit number (extensions, international) is shown as entered.
 */
export function formatPhone(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    let digits = value.replace(/\D/g, '');

    if (digits.length === 11 && digits.startsWith('1')) {
        digits = digits.slice(1);
    }

    if (digits.length !== 10) {
        return value.trim();
    }

    return `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
}

/**
 * Formats a phone number while it's being typed: 999, 999-999, 999-999-9999.
 */
export function formatPhoneAsTyped(value: string): string {
    let digits = value.replace(/\D/g, '');

    if (digits.length === 11 && digits.startsWith('1')) {
        digits = digits.slice(1);
    }

    digits = digits.slice(0, 10);

    if (digits.length <= 3) {
        return digits;
    }

    if (digits.length <= 6) {
        return `${digits.slice(0, 3)}-${digits.slice(3)}`;
    }

    return `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
}
