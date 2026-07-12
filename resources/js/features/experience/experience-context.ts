import { createContext, useContext } from 'react';
import type { ExperienceContextValue } from './types';

export const ExperienceContext = createContext<ExperienceContextValue | null>(
    null,
);

export const useExperience = (): ExperienceContextValue => {
    const value = useContext(ExperienceContext);

    if (!value) {
        throw new Error(
            'useExperience must be used inside ExperienceProvider.',
        );
    }

    return value;
};
