import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

import enCommon from './locales/en/common.json'
import enAuth from './locales/en/auth.json'
import enAccount from './locales/en/account.json'
import enAdmin from './locales/en/admin.json'
import enLiturgy from './locales/en/liturgy.json'
import enMore from './locales/en/more.json'
import enOauth from './locales/en/oauth.json'
import enErrors from './locales/en/errors.json'

import arCommon from './locales/ar/common.json'
import arAuth from './locales/ar/auth.json'
import arAccount from './locales/ar/account.json'
import arAdmin from './locales/ar/admin.json'
import arLiturgy from './locales/ar/liturgy.json'
import arMore from './locales/ar/more.json'
import arOauth from './locales/ar/oauth.json'
import arErrors from './locales/ar/errors.json'

export const defaultNS = 'common'

i18n.use(initReactI18next).init({
    lng: 'en',
    fallbackLng: 'en',
    defaultNS,
    ns: ['common', 'auth', 'account', 'admin', 'liturgy', 'more', 'oauth', 'errors'],
    resources: {
        en: {
            common: enCommon,
            auth: enAuth,
            account: enAccount,
            admin: enAdmin,
            liturgy: enLiturgy,
            more: enMore,
            oauth: enOauth,
            errors: enErrors,
        },
        ar: {
            common: arCommon,
            auth: arAuth,
            account: arAccount,
            admin: arAdmin,
            liturgy: arLiturgy,
            more: arMore,
            oauth: arOauth,
            errors: arErrors,
        },
    },
    interpolation: {
        escapeValue: false,
    },
})

export default i18n
