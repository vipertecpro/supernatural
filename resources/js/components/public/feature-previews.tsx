import { EyeOff, FileCheck2, LockKeyhole, Waypoints } from 'lucide-react';

export function ArchiveRecordStack() {
    return (
        <div className="preview-frame record-stack" aria-hidden="true">
            <div className="record-sheet record-sheet-back" />
            <div className="record-sheet record-sheet-middle" />
            <div className="record-sheet record-sheet-front">
                <span>RECORD / 001</span>
                <i />
                <i />
                <i />
                <b>Connected knowledge</b>
            </div>
        </div>
    );
}

export function JourneyPathPreview() {
    return (
        <div className="preview-frame journey-preview" aria-hidden="true">
            <span className="journey-line" />
            {['Begin', 'Return', 'Continue', 'Remember'].map((label, index) => (
                <span
                    key={label}
                    className="journey-node"
                    style={{ '--node-index': index } as React.CSSProperties}
                >
                    <i />
                    <b>{label}</b>
                </span>
            ))}
            <LockKeyhole className="journey-lock" />
        </div>
    );
}

export function EvidenceGraphPreview() {
    const nodes = [
        ['A', 18, 52],
        ['B', 45, 22],
        ['C', 52, 68],
        ['D', 78, 38],
        ['E', 84, 76],
    ];

    return (
        <div className="preview-frame evidence-graph" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M18 52 45 22 78 38 84 76 52 68 18 52M45 22 52 68M78 38 52 68" />
            </svg>
            {nodes.map(([label, left, top]) => (
                <span key={label} style={{ left: `${left}%`, top: `${top}%` }}>
                    {label}
                </span>
            ))}
        </div>
    );
}

export function SpoilerStatePreview() {
    return (
        <div className="preview-frame spoiler-preview" aria-hidden="true">
            <div>
                <span>Visible</span>
                <b>Field report: safe context</b>
            </div>
            <div>
                <span>Warning</span>
                <b>Reveal beyond your boundary</b>
            </div>
            <div>
                <EyeOff />
                <b>Details withheld</b>
            </div>
        </div>
    );
}

export function BunkerNetworkPreview() {
    return (
        <div className="preview-frame bunker-preview" aria-hidden="true">
            <Waypoints />
            <span className="bunker-ring bunker-ring-one" />
            <span className="bunker-ring bunker-ring-two" />
            <span className="bunker-dot bunker-dot-one" />
            <span className="bunker-dot bunker-dot-two" />
            <span className="bunker-dot bunker-dot-three" />
        </div>
    );
}

export function SourceLedgerPreview() {
    return (
        <div className="preview-frame source-preview" aria-hidden="true">
            <FileCheck2 />
            <div>
                <span>SOURCE</span>
                <b>Provenance recorded</b>
            </div>
            <div>
                <span>RIGHTS</span>
                <b>Use assessed</b>
            </div>
            <div>
                <span>REVIEW</span>
                <b>Decision attributable</b>
            </div>
        </div>
    );
}
