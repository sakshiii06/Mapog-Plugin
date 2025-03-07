import { useState, useEffect } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import $ from 'jquery';

const Edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    const { selectedMap } = attributes;
    const [maps, setMaps] = useState([]);
    const [isLoggedIn, setIsLoggedIn] = useState(true); // Track login status

    useEffect(() => {
        const accessToken = localStorage.getItem('mapog_access_token');

        if (!accessToken) {
            setIsLoggedIn(false); // Not logged in
            return;
        }

        const baseUrl = window.mapogConfig?.base_url || '';
        setIsLoggedIn(true); // User is logged in

        $.ajax({
            url: window.mapogConfig?.maps_api || '',
            method: 'GET',
            headers: { 'Authorization': accessToken }, // Added "Bearer "
            success: function(response) {
                console.log("API Response:", response); // Debugging API response
                
                if (response?.data?.data?.length > 0) {
                    const fetchedMaps = response.data.data.map(map => ({
                        map_name: map.map_name,
                        mapid: map.mapid,
                        url: `${baseUrl}${map.map_name}/${map.mapid}`
                    }));
                    console.log("Fetched Maps:", fetchedMaps); // Debugging fetched maps
                    setMaps(fetchedMaps);
                } else {
                    console.warn("No maps found in response:", response);
                }
            },
            error: function(error) {
                console.error("Error fetching maps:", error);
            }
        });
    }, []);

    // Handle selecting a map
    const selectMap = (event) => {
        const mapId = event.target.value;
        console.log("Selected Map ID:", mapId); // Debugging selected ID
        
        const selected = maps.find(map => map.mapid === mapId);
        console.log("Selected Map:", selected); // Debugging selected map
        
        if (selected) {
            setAttributes({ selectedMap: selected.url });
        }
    };

    return (
        <div {...blockProps}>
            {isLoggedIn ? (
                <>
                    <h3>Your Maps</h3>
                    <select onChange={selectMap} style={{ width: '100%', padding: '8px', fontSize: '16px', marginBottom: '10px' }}>
                        <option value="">Select a map...</option>
                        {maps.length > 0 ? (
                            maps.map((map) => (
                                <option key={map.mapid} value={map.mapid}>{map.map_name}</option>
                            ))
                        ) : (
                            <option disabled>No maps available</option>
                        )}
                    </select>
                    {selectedMap && (
                        <div className="map-iframe">
                            <iframe src={selectedMap} width="100%" height="500"></iframe>
                        </div>
                    )}
                </>
            ) : (
                <p style={{ 
                    color: '#D32F2F', 
                    fontSize: '18px', 
                    fontWeight: 'bold', 
                    background: '#FFEBEE', 
                    padding: '12px', 
                    borderRadius: '8px', 
                    textAlign: 'center',
                    border: '1px solid #D32F2F',
                    marginTop: '10px' 
                }}>
                    🔒 You need to log in to access your maps.
                </p>
            )}
        </div>
    );
};

export default Edit;


