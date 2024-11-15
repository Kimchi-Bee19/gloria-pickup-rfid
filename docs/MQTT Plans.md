rough idea nya gini untuk yang IOT-based reader:
client-id nya mac address

on connect:

publish to
/dev/readers/{username}/status
data: connected, ip_addr, other info
for last will testament, set the same topic to disconnected
use retain=true

subscribe to /dev/readers/{username}/config
get the data, use the data as config, if a new data comes from there, then update the local config to match


on tag auth (vehicle):
publish to
/events/v1/vehicle_arrival
data: ???

on tag auth (student):
publish to
/events/v1/student_departure
data: ???