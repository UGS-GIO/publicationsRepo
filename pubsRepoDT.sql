-- First, let's create the necessary tables if they don't exist in the pubs schema
CREATE TABLE IF NOT EXISTS pubs.ugspubs (
    series_id TEXT PRIMARY KEY,
    pub_year INTEGER,
    pub_month INTEGER,
    pub_name TEXT,
    pub_author TEXT,
    pub_sec_author TEXT,
    pub_url TEXT,
    pub_publisher TEXT,
    pub_scale TEXT,
    keywords TEXT,
    bookstore_url TEXT,
    quad_name TEXT,
    servname TEXT,
    cam_offset TEXT,
    lat NUMERIC,
    longitude NUMERIC,
    popupfl TEXT,
    pubprevlink TEXT,
    pubprevlink2 TEXT,
    pubprevlink3 TEXT,
    series TEXT
);

CREATE TABLE IF NOT EXISTS pubs.attached_data (
    id SERIAL PRIMARY KEY,
    series_id TEXT REFERENCES pubs.ugspubs(series_id),
    extra_data TEXT,
    pub_url TEXT
);

-- Create the function to return publications data
CREATE OR REPLACE FUNCTION pubs.get_publications()
RETURNS SETOF json AS $$
DECLARE
    pub_record RECORD;
    attached_data_records RECORD;
    result_array jsonb[] := ARRAY[]::jsonb[];
    popup_content TEXT;
    popup_link TEXT;
    doi_link TEXT;
    bookstore_url_string TEXT;
    pub_name_formatted TEXT;
    string_data TEXT;
    map_type TEXT;
BEGIN
    FOR pub_record IN 
        SELECT *
        FROM pubs.ugspubs
        WHERE series_id NOT LIKE 'WCD%'
        AND COALESCE(keywords, '') NOT LIKE '%emmd%'
        AND COALESCE(keywords, '') NOT LIKE '%hmdc%'
        ORDER BY pub_year DESC, pub_month DESC
    LOOP
        -- Initialize popup content
        popup_content := '';
        popup_link := '';
        string_data := '';
        map_type := '';
        
        -- Handle publication URL
        IF pub_record.pub_url IS NOT NULL AND pub_record.pub_url != '' THEN
            popup_content := format(
                '<div id=\"downloadLink\"><div id=\"leftAlign\"><a href=\"%s\" target=\"_blank\">Publication</div>'
                '<div id=\"rightAlign\"><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></a></div></div><br><hr>',
                pub_record.pub_url
            );
            
            popup_link := format(
                '<div id=''clickMe'' onclick=''getElementById("modalText").innerHTML ="%s"''>'
                '<img src="https://geology.utah.gov/docs/images/down-arrow.png" width="16px"></div>',
                popup_content
            );
        END IF;

        -- Add service preview map URL
        CASE pub_record.servname
            WHEN '30x60_Quads', 'Other_Quads', 'FigureMaps' THEN
                popup_content := popup_content || format(
                    '<div id=\"downloadLink\"><div id=\"leftAlign\">'
                    '<a href=\"https://geology.utah.gov/apps/intgeomap/index.html?sid=%s&layers=100k\" target=\"_blank\">'
                    'Interactive Map</div><div id=\"rightAlign\">'
                    '<img src=\"https://geology.utah.gov/docs/images/map.png\" width=\"16\"></a></div></div><br><hr>',
                    pub_record.series_id
                );
            WHEN '7_5_Quads', 'MD_24K' THEN
                popup_content := popup_content || format(
                    '<div id=\"downloadLink\"><div id=\"leftAlign\">'
                    '<a href=\"https://geology.utah.gov/apps/intgeomap/index.html?sid=%s&layers=24k\" target=\"_blank\">'
                    'Interactive Map</div><div id=\"rightAlign\">'
                    '<img src=\"https://geology.utah.gov/docs/images/map.png\" width=\"16\"></a></div></div><br><hr>',
                    pub_record.series_id
                );
            WHEN '500k_Statewide' THEN
                popup_content := popup_content || format(
                    '<div id=\"downloadLink\"><div id=\"leftAlign\">'
                    '<a href=\"https://geology.utah.gov/apps/intgeomap/index.html?sid=%s&layers=500k\" target=\"_blank\">'
                    'Interactive Map</div><div id=\"rightAlign\">'
                    '<img src=\"https://geology.utah.gov/docs/images/map.png\" width=\"16\"></a></div></div><br><hr>',
                    pub_record.series_id
                );
        END CASE;

        -- Process attached data
        FOR attached_data_records IN 
            SELECT * FROM pubs.attached_data 
            WHERE series_id = pub_record.series_id 
            ORDER BY extra_data ASC
        LOOP
            -- Handle different types of attached data
            IF attached_data_records.extra_data = 'Lithologic Column' THEN
                -- Handle lithologic column
                NULL;
            ELSIF attached_data_records.extra_data = 'Cross Section' THEN
                -- Handle cross section
                NULL;
            ELSIF attached_data_records.pub_url LIKE 'http%' THEN
                string_data := string_data || format(
                    '<a href=''%s'' target=''_blank''>%s</a><br>',
                    attached_data_records.pub_url,
                    attached_data_records.extra_data
                );
                
                popup_content := popup_content || format(
                    '<div id=\"downloadLink\"><div id=\"leftAlign\">'
                    '<a href=\"%s\" target=\"_blank\">%s</div>'
                    '<div id=\"rightAlign\">'
                    '<img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\">'
                    '</a></div></div><br><hr>',
                    attached_data_records.pub_url,
                    attached_data_records.extra_data
                );
            ELSE
                string_data := string_data || format(
                    '<a href=''https://ugspub.nr.utah.gov/publications/%s'' target=''_blank'' download>%s</a><br>',
                    attached_data_records.pub_url,
                    attached_data_records.extra_data
                );
                
                popup_content := popup_content || format(
                    '<div id=\"downloadLink\"><div id=\"leftAlign\">'
                    '<a href=\"https://ugspub.nr.utah.gov/publications/%s\" target=\"_blank\" download>%s</div>'
                    '<div id=\"rightAlign\">'
                    '<img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\">'
                    '</a></div></div><br><hr>',
                    attached_data_records.pub_url,
                    attached_data_records.extra_data
                );
            END IF;

            -- Update map_type based on extra_data
            IF attached_data_records.extra_data = 'GeoTiff - Zip' THEN
                map_type := map_type || 'raster ';
            END IF;
            IF attached_data_records.extra_data = 'GIS Data - Zip' THEN
                map_type := map_type || 'vector ';
            END IF;

            -- Update popup_link
            popup_link := format(
                '<div id=''clickMe'' onclick=''getElementById("modalText").innerHTML ="%s"''>'
                '<img src="https://geology.utah.gov/docs/images/down-arrow.png" width="16px"></div>',
                popup_content
            );
        END LOOP;

        -- Append map type information to popup_link
        IF map_type LIKE '%raster%' THEN
            popup_link := popup_link || '<div style=''display: none''>Raster Map </div>';
        END IF;
        IF map_type LIKE '%vector%' THEN
            popup_link := popup_link || '<div style=''display: none''>Vector Map </div>';
        END IF;

        -- Generate DOI link
        IF pub_record.series_id LIKE 'MO-%' THEN
            doi_link := 'https://doi.org/10.34191/' || substring(pub_record.series_id from 1 for 4);
        ELSIF (pub_record.pub_publisher LIKE '%UGS%' OR pub_record.pub_publisher LIKE '%UGMS%') 
            AND pub_record.series_id NOT LIKE 'HD-%' THEN
            doi_link := 'https://doi.org/10.34191/' || pub_record.series_id;
        END IF;

        -- Format publication name with DOI
        IF pub_record.pub_url IS NOT NULL AND pub_record.pub_url != '' THEN
            pub_name_formatted := format(
                '<div class="pubTitle"><a href="%s" target="_blank">%s '
                '<img src="https://geology.utah.gov/docs/images/pdf16x16.gif"></a></div>'
                '<div class="smallDOI"><a href="%s" target="_blank">%s</a></div>',
                pub_record.pub_url, pub_record.pub_name, doi_link, doi_link
            );
        ELSE
            pub_name_formatted := pub_record.pub_name;
        END IF;

        -- Handle bookstore URL
        IF pub_record.bookstore_url IS NOT NULL AND pub_record.bookstore_url != '' THEN
            IF pub_record.series_id LIKE 'MO-%' THEN
                bookstore_url_string := format(
                    '<a href="https://utahmapstore.com/products/%s" target="_blank">'
                    '<img src="https://geology.utah.gov/docs/images/buy.png" width="16"></a>',
                    substring(pub_record.series_id from 1 for 4)
                );
            ELSE
                bookstore_url_string := format(
                    '<a href="https://utahmapstore.com/products/%s" target="_blank">'
                    '<img src="https://geology.utah.gov/docs/images/buy.png" width="16"></a>',
                    pub_record.series_id
                );
            END IF;
        ELSE
            bookstore_url_string := '';
        END IF;

        -- Add to result array
        result_array := result_array || jsonb_build_object(
            'series_id', pub_record.series_id,
            'pub_year', pub_record.pub_year,
            'pub_name', pub_name_formatted,
            'pub_author', COALESCE(pub_record.pub_author, '') || COALESCE(pub_record.pub_sec_author, ''),
            'pub_scale', pub_record.pub_scale,
            'keywords', pub_record.keywords,
            'buy_link4AlphList', bookstore_url_string,
            'series', pub_record.series,
            'linksInPopup', popup_link
        );
    END LOOP;

    RETURN QUERY SELECT json_agg(elem)::json FROM unnest(result_array) elem;
END;
$$ LANGUAGE plpgsql;

-- Grant necessary permissions for PostgREST
GRANT USAGE ON SCHEMA pubs TO web_anon;
GRANT EXECUTE ON FUNCTION pubs.get_publications TO web_anon;
