export function formatDateDMY(value: string | null | undefined): string | null {
    if (!value) {
        return null
    }

    const [year, month, day] = value.split('-')

    if (!year || !month || !day) {
        return value
    }

    return `${day}-${month}-${year}`
}

/**
 * Parse a "dd-mm-yyyy" string into an ISO "yyyy-mm-dd" date, or null if the
 * input doesn't fully match that shape yet (e.g. still being typed).
 */
export function parseDateDMY(value: string): string | null {
    const match = value.trim().match(/^(\d{2})-(\d{2})-(\d{4})$/)

    if (!match) {
        return null
    }

    const [, day, month, year] = match

    return `${year}-${month}-${day}`
}

/**
 * Compute a whole-years-old age from an ISO "yyyy-mm-dd" date of birth, as
 * of the given reference date (defaults to today, also ISO "yyyy-mm-dd").
 */
export function calculateAge(dateOfBirth: string | null | undefined, asOf?: string): number | null {
    if (!dateOfBirth) {
        return null
    }

    const [year, month, day] = dateOfBirth.split('-').map(Number)

    if (!year || !month || !day) {
        return null
    }

    const [refYear, refMonth, refDay] = asOf ? asOf.split('-').map(Number) : [0, 0, 0]
    const today = new Date()
    const referenceYear = asOf ? refYear : today.getFullYear()
    const referenceMonth = asOf ? refMonth : today.getMonth() + 1
    const referenceDay = asOf ? refDay : today.getDate()

    let age = referenceYear - year
    const hasHadBirthdayByReference = referenceMonth > month || (referenceMonth === month && referenceDay >= day)

    if (!hasHadBirthdayByReference) {
        age -= 1
    }

    return age
}

/**
 * Whether an ISO "yyyy-mm-dd" date of birth falls on today's month and day.
 */
export function isBirthdayToday(dateOfBirth: string | null | undefined): boolean {
    if (!dateOfBirth) {
        return false
    }

    const [, month, day] = dateOfBirth.split('-').map(Number)

    if (!month || !day) {
        return false
    }

    const today = new Date()

    return today.getMonth() + 1 === month && today.getDate() === day
}
