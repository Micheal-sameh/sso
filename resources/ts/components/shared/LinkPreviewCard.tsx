interface LinkPreviewCardProps {
    url: string
    title: string | null
    description: string | null
    image: string | null
    onOpen: (url: string) => void
}

export default function LinkPreviewCard({ url, title, description, image, onOpen }: LinkPreviewCardProps) {
    let host = url
    try {
        host = new URL(url).host
    } catch {
        // keep raw url as the label
    }

    return (
        <button
            type="button"
            className="link-preview-card border rounded overflow-hidden text-start w-100 bg-white p-0"
            onClick={() => onOpen(url)}
        >
            {image && <img src={image} alt="" className="w-100 object-fit-cover" style={{ maxHeight: 220 }} />}
            <div className="p-3">
                <div className="text-ava-muted text-tiny text-uppercase mb-1">{host}</div>
                <div className="fw-semibold text-truncate-2">{title ?? url}</div>
                {description && <div className="text-ava-muted text-tiny mt-1 text-truncate-2">{description}</div>}
            </div>
        </button>
    )
}
